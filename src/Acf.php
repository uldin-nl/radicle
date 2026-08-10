<?php

namespace Uldin\Radicle;

use ReflectionClass;
use ReflectionMethod;
use Throwable;
use Illuminate\Support\Facades\Vite;
use Roots\Acorn\Application;
use Uldin\Radicle\Support\Acf as AcfFieldGroup;

class Acf
{
    /**
     * Component layouts indexed by their registered block name.
     */
    protected array $componentLayouts = [];

    /**
     * The application instance.
     *
     * @var \Roots\Acorn\Application
     */
    protected $app;

    /**
     * Create a new radicle instance.
     *
     * @param  \Roots\Acorn\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Boot the Acf client.
     *
     * @return void
     */
    public function boot()
    {
        if(function_exists('acf_add_options_page') ) {
            foreach (config('acf.options_pages', []) as $options_page) {
                $parent_slug = $options_page['menu_slug'];
                acf_add_options_page($options_page);

                if (function_exists('acf_add_options_sub_page')) {
                    if (isset($options_page['sub_pages'])) {
                        foreach ($options_page['sub_pages'] as $sub_page) {
                            acf_add_options_sub_page(['parent_slug' => $parent_slug, ...$sub_page]);
                        }
                    }
                }
            }
        }

        if(function_exists('acf_add_local_field_group')) {
            $acfFiles = $this->getAllAcfClasses();
            foreach ($acfFiles as $acfFile) {
                $class = "App\Acf\\" . str_replace('.php', '', $acfFile);
                $class = new $class();
                acf_add_local_field_group($class->build());
            }
        }

        $this->bootComponentBlocks();
    }

    /**
     * Register the layouts in the application's component class as ACF blocks.
     */
    protected function bootComponentBlocks(): void
    {
        if (!config('acf.component_blocks.enabled', false)) {
            return;
        }

        if (config('acf.component_blocks.force_block_editor', false)) {
            $this->enableBlockEditorForComponentPostTypes();
        }
        $this->enqueueComponentBlockEditorStyle();
        $this->hideLegacyFlexibleContentGroups();

        if (!function_exists('acf_register_block_type')
            || !function_exists('acf_add_local_field_group')) {
            return;
        }

        $componentClass = config('acf.component_blocks.class');

        if (!$componentClass || !class_exists($componentClass)) {
            return;
        }

        $this->registerComponentBlockCategory();

        $layouts = $this->getComponentLayouts($componentClass);

        foreach ($layouts as $layout) {
            if (!empty($layout['name'])) {
                $this->componentLayouts[sanitize_title(str_replace('_', '-', $layout['name']))] = $layout;
            }

            $this->registerComponentBlock($layout);
        }

        $this->restrictBlockTypes();
        $this->registerLegacyContentMigration();
        $this->registerFlexibleContentSync();
        $this->registerBlockToFlexibleContentSync();
    }

    /**
     * Enable the WordPress block editor for configured component post types.
     */
    protected function enableBlockEditorForComponentPostTypes(): void
    {
        $postTypes = config('acf.component_blocks.post_types', ['page']);

        add_filter('use_block_editor_for_post_type', function ($useBlockEditor, string $postType) use ($postTypes) {
            return in_array($postType, $postTypes, true) ? true : $useBlockEditor;
        }, 100, 2);

        add_filter('use_block_editor_for_post', function ($useBlockEditor, $post) use ($postTypes) {
            return isset($post->post_type) && in_array($post->post_type, $postTypes, true)
                ? true
                : $useBlockEditor;
        }, 100, 2);
    }

    /**
     * Get layouts explicitly from layouts(), or discover public static *Layout methods.
     */
    protected function getComponentLayouts(string $componentClass): array
    {
        if (method_exists($componentClass, 'layouts')) {
            return array_values(array_filter($componentClass::layouts(), 'is_array'));
        }

        $reflection = new ReflectionClass($componentClass);
        $layouts = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $componentClass
                || !str_ends_with($method->getName(), 'Layout')
                || $method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            $layout = $method->invoke(null);

            if (is_array($layout)) {
                $layouts[] = $layout;
            }
        }

        return $layouts;
    }

    /**
     * Register one Flexible Content-style layout as a standalone ACF block.
     */
    protected function registerComponentBlock(array $layout): void
    {
        if (empty($layout['name']) || empty($layout['label']) || empty($layout['sub_fields'])) {
            return;
        }

        $name = sanitize_title(str_replace('_', '-', $layout['name']));
        $view = $layout['view'] ?? $layout['name'];
        $category = config('acf.component_blocks.category.slug', 'components');
        $fieldGroupKey = 'group_block_'.str_replace('-', '_', $name);
        $fieldGroupBuilder = new class($fieldGroupKey) extends AcfFieldGroup {
            public function __construct(string $key)
            {
                $this->key = $key;
            }
        };
        $blockFields = $fieldGroupBuilder->autoKeyGenerate($layout['sub_fields']);

        acf_register_block_type([
            'name' => $name,
            'title' => $layout['label'],
            'description' => $layout['description'] ?? '',
            'category' => $layout['category'] ?? $category,
            'icon' => $layout['icon'] ?? 'layout',
            'keywords' => $layout['keywords'] ?? [],
            'supports' => $layout['supports'] ?? [
                'align' => false,
                'jsx' => true,
            ],
            'render_callback' => function ($block, $content = '', $isPreview = false, $postId = 0) use ($view, $blockFields) {
                $postId = $postId ?: get_the_ID();
                $viewPath = trim(config('acf.component_blocks.view_path', 'partials'), '.');
                $viewName = $viewPath.'.'.str_replace('/', '.', $view);
                $blockData = $block['data'] ?? [];
                $fields = get_fields() ?: [];

                if (config('acf.component_blocks.data_source') === 'flexible_content'
                    && isset($blockData['_radicle_flexible_index'])) {
                    $rows = get_field(config('acf.component_blocks.flexible_content_field', 'content'), $postId, false) ?: [];
                    $blockData = $rows[(int) $blockData['_radicle_flexible_index']] ?? [];
                    unset($blockData['acf_fc_layout']);
                    $fields = [];
                }

                $this->addComponentFieldLoop($blockFields, $blockData, $postId);

                try {
                    echo view($viewName, array_merge($fields, [
                        'fields' => $fields,
                        'block' => $block,
                        'content' => $content,
                        'isPreview' => $isPreview,
                        'postId' => $postId,
                    ]))->render();
                } finally {
                    acf_remove_loop('active');
                }
            },
        ]);

        if (config('acf.component_blocks.data_source', 'block') === 'block') {
            acf_add_local_field_group([
                'key' => $fieldGroupKey,
                'title' => $layout['label'],
                'fields' => $blockFields,
                'location' => [[[
                    'param' => 'block',
                    'operator' => '==',
                    'value' => 'acf/'.$name,
                ]]],
            ]);
        }
    }

    /**
     * Keep preview-only Gutenberg blocks in sync with Flexible Content rows.
     */
    protected function registerFlexibleContentSync(): void
    {
        add_action('acf/save_post', function ($postId): void {
            if (!is_numeric($postId) || wp_is_post_revision((int) $postId)) {
                return;
            }

            $dataSource = config('acf.component_blocks.data_source', 'block');

            // In block mode this direction is only needed after a Classic/ACF save.
            if ($dataSource === 'block' && empty($_POST['acf'])) {
                return;
            }

            $post = get_post((int) $postId);
            $postTypes = config('acf.component_blocks.post_types', ['page']);

            if (!$post || !in_array($post->post_type, $postTypes, true)) {
                return;
            }

            $field = config('acf.component_blocks.flexible_content_field', 'content');
            $rows = get_field($field, (int) $postId, false);

            if (!is_array($rows)) {
                return;
            }

            $blocks = [];

            foreach ($rows as $index => $row) {
                $layout = $row['acf_fc_layout'] ?? null;

                if (!$layout) {
                    continue;
                }

                $name = sanitize_title(str_replace('_', '-', $layout));
                unset($row['acf_fc_layout']);

                $blocks[] = serialize_block([
                    'blockName' => 'acf/'.$name,
                    'attrs' => [
                        'name' => 'acf/'.$name,
                        'data' => $dataSource === 'flexible_content'
                            ? ['_radicle_flexible_index' => $index]
                            : $row,
                        'mode' => 'preview',
                    ],
                    'innerBlocks' => [],
                    'innerHTML' => '',
                    'innerContent' => [],
                ]);
            }

            wp_update_post([
                'ID' => (int) $postId,
                'post_content' => wp_slash(implode("\n\n", $blocks)),
            ]);
        }, 20);
    }

    /**
     * Migrate legacy Flexible Content when a page first opens in Gutenberg.
     */
    protected function registerLegacyContentMigration(): void
    {
        if (!config('acf.component_blocks.auto_migrate_legacy_content', true)) {
            return;
        }

        add_action('current_screen', function ($screen): void {
            if (!$screen || !method_exists($screen, 'is_block_editor') || !$screen->is_block_editor()) {
                return;
            }

            $postId = isset($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;

            if ($postId) {
                $this->migrateLegacyPostToBlocks($postId);
            }
        });
    }

    /**
     * Convert an untouched legacy component page to full ACF block data.
     */
    protected function migrateLegacyPostToBlocks(int $postId): bool
    {
        $post = get_post($postId);

        if (!$post
            || !in_array($post->post_type, config('acf.component_blocks.post_types', ['page']), true)
            || trim((string) $post->post_content) !== '') {
            return false;
        }

        $field = config('acf.component_blocks.flexible_content_field', 'content');
        $rows = get_field($field, $postId, false);

        if (!is_array($rows) || !$rows) {
            return false;
        }

        $blocks = [];

        foreach ($rows as $row) {
            $layoutName = $row['acf_fc_layout'] ?? null;

            if (!$layoutName) {
                continue;
            }

            $blockName = sanitize_title(str_replace('_', '-', $layoutName));

            if (!isset($this->componentLayouts[$blockName])) {
                continue;
            }

            unset($row['acf_fc_layout']);
            $blocks[] = serialize_block([
                'blockName' => 'acf/'.$blockName,
                'attrs' => [
                    'name' => 'acf/'.$blockName,
                    'data' => $row,
                    'mode' => 'preview',
                ],
                'innerBlocks' => [],
                'innerHTML' => '',
                'innerContent' => [],
            ]);
        }

        if (!$blocks) {
            return false;
        }

        add_post_meta($postId, '_radicle_21_block_migration_backup', [
            'created_at' => current_time('mysql'),
            'post_content' => $post->post_content,
        ], true);

        $result = wp_update_post([
            'ID' => $postId,
            'post_content' => wp_slash(implode("\n\n", $blocks)),
        ], true);

        return !is_wp_error($result);
    }

    /**
     * Synchronize Gutenberg ACF block values back to Flexible Content on save.
     */
    protected function registerBlockToFlexibleContentSync(): void
    {
        if (config('acf.component_blocks.data_source', 'block') !== 'block') {
            return;
        }

        add_action('save_post', function ($postId, $post): void {
            static $syncing = false;

            if ($syncing
                || !$post instanceof \WP_Post
                || wp_is_post_revision((int) $postId)
                || wp_is_post_autosave((int) $postId)
                || !empty($_POST['acf'])
                || !in_array($post->post_type, config('acf.component_blocks.post_types', ['page']), true)) {
                return;
            }

            $rows = [];

            foreach (parse_blocks($post->post_content) as $block) {
                $blockName = (string) ($block['blockName'] ?? '');

                if (!str_starts_with($blockName, 'acf/')) {
                    continue;
                }

                $name = substr($blockName, 4);
                $layout = $this->componentLayouts[$name] ?? null;

                if (!$layout) {
                    continue;
                }

                $fieldGroupKey = 'group_block_'.str_replace('-', '_', $name);
                $fields = acf_get_fields($fieldGroupKey) ?: [];
                $keyedValues = $this->mapComponentFieldValues($fields, $block['attrs']['data'] ?? []);
                $rows[] = array_merge(
                    ['acf_fc_layout' => $layout['name']],
                    $this->nameComponentFieldValues($fields, $keyedValues)
                );
            }

            if (!$rows) {
                return;
            }

            $syncing = true;
            update_field(
                config('acf.component_blocks.flexible_content_field', 'content'),
                $rows,
                (int) $postId
            );
            $syncing = false;
        }, 30, 2);
    }

    /**
     * Convert generated field-key values to the names expected by Flexible Content.
     */
    protected function nameComponentFieldValues(array $fields, array $values): array
    {
        $named = [];

        foreach ($fields as $field) {
            if (!array_key_exists($field['key'], $values)) {
                continue;
            }

            $value = $values[$field['key']];

            if (($field['type'] ?? null) === 'repeater' && is_array($value)) {
                $value = array_map(
                    fn (array $row): array => $this->nameComponentFieldValues($field['sub_fields'] ?? [], $row),
                    $value
                );
            } elseif (($field['type'] ?? null) === 'group' && is_array($value)) {
                $value = $this->nameComponentFieldValues($field['sub_fields'] ?? [], $value);
            }

            $named[$field['name']] = $value;
        }

        return $named;
    }

    /**
     * Make legacy Flexible Content helpers work while rendering a block partial.
     */
    protected function addComponentFieldLoop(array $fields, array $data, $postId): void
    {
        $fields = $this->prepareComponentFields($fields);
        $row = $this->mapComponentFieldValues($fields, $data);

        acf_add_loop([
            'name' => 'component_block',
            'value' => [$row],
            'field' => [
                'type' => 'group',
                'sub_fields' => $fields,
            ],
            'i' => 0,
            'post_id' => $postId,
        ]);
    }

    /**
     * Add the internal names ACF normally prepares for registered field groups.
     */
    protected function prepareComponentFields(array $fields): array
    {
        return array_map(function (array $field): array {
            $field = acf_get_field($field['key']) ?: $field;
            $field['_name'] = $field['_name'] ?? $field['name'];

            if (!empty($field['sub_fields'])) {
                $field['sub_fields'] = $this->prepareComponentFields($field['sub_fields']);
            }

            return $field;
        }, $fields);
    }

    /**
     * Map values from legacy or current field keys onto the block field keys.
     */
    protected function mapComponentFieldValues(array $fields, array $data): array
    {
        $row = [];

        foreach ($fields as $field) {
            $valueFound = false;
            $value = null;

            foreach ([$field['key'], $field['name']] as $candidate) {
                if (array_key_exists($candidate, $data)) {
                    $value = $data[$candidate];
                    $valueFound = true;
                    break;
                }
            }

            if (!$valueFound) {
                foreach ($data as $key => $candidateValue) {
                    if (str_ends_with((string) $key, '_'.$field['name'])) {
                        $value = $candidateValue;
                        $valueFound = true;
                        break;
                    }
                }
            }

            if (!$valueFound) {
                continue;
            }

            if (($field['type'] ?? null) === 'repeater') {
                if (is_array($value)) {
                    $value = array_map(
                        fn (array $repeaterRow): array => $this->mapComponentFieldValues($field['sub_fields'] ?? [], $repeaterRow),
                        $value
                    );
                } else {
                    $rows = [];

                    for ($index = 0; $index < (int) $value; $index++) {
                        $rows[] = $this->mapComponentFieldValues(
                            $field['sub_fields'] ?? [],
                            $this->getNestedComponentData($data, $field['name'].'_'.$index.'_')
                        );
                    }

                    $value = $rows;
                }
            } elseif (($field['type'] ?? null) === 'group') {
                $value = $this->mapComponentFieldValues(
                    $field['sub_fields'] ?? [],
                    is_array($value) ? $value : $this->getNestedComponentData($data, $field['name'].'_')
                );
            }

            $row[$field['key']] = $value;
        }

        return $row;
    }

    /**
     * Extract flattened ACF block values below a field-name prefix.
     */
    protected function getNestedComponentData(array $data, string $prefix): array
    {
        $nested = [];

        foreach ($data as $key => $value) {
            if (str_starts_with((string) $key, '_') || !str_starts_with((string) $key, $prefix)) {
                continue;
            }

            $nested[substr((string) $key, strlen($prefix))] = $value;
        }

        return $nested;
    }

    /**
     * Load the project's frontend stylesheet inside the block editor iframe.
     */
    protected function enqueueComponentBlockEditorStyle(): void
    {
        $entryPoint = config('acf.component_blocks.editor_style');

        if (!$entryPoint) {
            return;
        }

        add_filter('block_editor_settings_all', function (array $settings) use ($entryPoint): array {
            try {
                $style = Vite::asset($entryPoint);
            } catch (Throwable $exception) {
                return $settings;
            }

            $settings['styles'][] = [
                'css' => "@import url('{$style}')",
            ];

            $variables = $this->getEditorCssVariables();
            $css = '';

            if ($variables) {
                $css .= implode(', ', [
                    'html',
                    'body',
                    '.editor-styles-wrapper',
                    '.block-editor-block-list__layout',
                    '.acf-block-component',
                    '.acf-block-preview',
                ]).' { '.implode(' ', $variables).' }';
            }

            $linkColor = config('acf.component_blocks.editor_link_color');

            if ($linkColor) {
                $css .= ' .acf-block-preview a:not([class*="text-"]) {'
                    .' color: '.$linkColor.' !important; }';
            }

            if (config('acf.component_blocks.disable_editor_motion', true)) {
                $css .= ' .acf-block-preview, .acf-block-preview * {'
                    .' animation-delay: 0s !important; animation-duration: 0s !important;'
                    .' transition-delay: 0s !important; transition-duration: 0s !important; }'
                    .' .acf-block-preview .opacity-0 { opacity: 1 !important; }'
                    .' .acf-block-preview :is(.motion-reveal, .motion-stagger__item,'
                    .' .hero-motion__background, .hero-motion__line > span,'
                    .' .hero-motion__copy, .hero-motion__cta, .hero-motion__trusted,'
                    .' .motion-case-phone) { opacity: 1 !important; transform: none !important; }'
                    .' .acf-block-preview .translate-y-6 { transform: translateY(0) !important; }';
            }

            if ($css !== '') {
                $settings['styles'][] = [
                    'css' => $css,
                ];
            }

            return $settings;
        });
    }

    /**
     * Build sanitized CSS custom properties from ACF options.
     */
    protected function getEditorCssVariables(): array
    {
        $variables = [];
        $resolved = [];

        foreach (config('acf.component_blocks.editor_css_variables', []) as $property => $settings) {
            if (!preg_match('/^--[a-z0-9-]+$/i', (string) $property) || empty($settings['field'])) {
                continue;
            }

            if (!function_exists('get_field_object')
                || !get_field_object($settings['field'], 'option', false)) {
                continue;
            }

            $value = get_field($settings['field'], 'option');
            $value = $value !== null && $value !== '' ? $value : ($settings['fallback'] ?? '');

            if (($settings['type'] ?? 'color') === 'number') {
                $value = is_numeric($value) ? (float) $value : (float) ($settings['fallback'] ?? 0);
            } else {
                $value = sanitize_hex_color((string) $value) ?: sanitize_hex_color((string) ($settings['fallback'] ?? ''));
            }

            if ($value === '' || $value === null || $value === false) {
                continue;
            }

            $resolvedValue = $value.($settings['suffix'] ?? '');
            $resolved[$property] = $resolvedValue;
            $variables[] = $property.': '.$resolvedValue.';';
        }

        $tailwindTokens = [
            '--color-primary' => '--site-primary-color',
            '--color-orange' => '--site-primary-color',
            '--color-on-primary' => '--site-primary-text-color',
            '--color-secondary' => '--site-secondary-color',
            '--color-yellow' => '--site-secondary-color',
            '--color-on-secondary' => '--site-secondary-text-color',
            '--color-black' => '--site-dark-color',
            '--color-background' => '--site-background-color',
            '--color-surface' => '--site-surface-color',
            '--color-ink' => '--site-text-color',
            '--radius-site' => '--site-border-radius',
        ];

        foreach ($tailwindTokens as $token => $source) {
            if (isset($resolved[$source])) {
                $variables[] = $token.': '.$resolved[$source].';';
            }
        }

        return $variables;
    }

    /**
     * Limit the inserter to application component blocks when configured.
     */
    protected function restrictBlockTypes(): void
    {
        if (!config('acf.component_blocks.restrict_block_types', false)) {
            return;
        }

        add_filter('allowed_block_types_all', function ($allowedBlockTypes, $editorContext): array {
            $allowed = array_map(
                fn (string $name): string => 'acf/'.$name,
                array_keys($this->componentLayouts)
            );

            return array_values(array_unique(array_merge(
                $allowed,
                config('acf.component_blocks.additional_allowed_blocks', [])
            )));
        }, 100, 2);
    }

    /**
     * Hide legacy Flexible Content groups after a page has component blocks.
     */
    protected function hideLegacyFlexibleContentGroups(): void
    {
        if (!config('acf.component_blocks.hide_flexible_content_in_block_editor', true)) {
            return;
        }

        add_filter('acf/location/match_rule', function ($matches, array $rule, array $screen, array $fieldGroup) {
            if (!$matches) {
                return $matches;
            }

            $postId = $screen['post_id'] ?? null;

            if (!$postId && isset($_GET['post'])) {
                $postId = absint(wp_unslash($_GET['post']));
            }

            if (!$postId) {
                return $matches;
            }

            $post = get_post((int) $postId);

            if (!$post || !use_block_editor_for_post($post)) {
                return $matches;
            }

            $hasComponentBlocks = array_filter(
                parse_blocks((string) get_post_field('post_content', $postId)),
                fn (array $block): bool => str_starts_with((string) ($block['blockName'] ?? ''), 'acf/')
            );

            if (!$hasComponentBlocks) {
                return $matches;
            }

            foreach (acf_get_fields($fieldGroup) ?: [] as $field) {
                if (($field['type'] ?? null) === 'flexible_content') {
                    return false;
                }
            }

            return $matches;
        }, 100, 4);
    }

    /**
     * Add the configured component category to the block inserter.
     */
    protected function registerComponentBlockCategory(): void
    {
        add_filter('block_categories_all', function (array $categories): array {
            $category = config('acf.component_blocks.category', []);

            if (empty($category['slug']) || empty($category['title'])) {
                return $categories;
            }

            foreach ($categories as $existingCategory) {
                if (($existingCategory['slug'] ?? null) === $category['slug']) {
                    return $categories;
                }
            }

            array_unshift($categories, $category);

            return $categories;
        });
    }

    public function getAcfPath()
    {
        return app_path() . "/Acf";
    }

    public function getAllAcfClasses()
    {
        $acfPath = $this->getAcfPath();
        if (!file_exists($acfPath)) {
            return [];
        }
        $acfFiles = scandir($acfPath);
        $acfClasses = [];
        foreach ($acfFiles as $acfFile) {
            if (strpos($acfFile, '.php') !== false) {
                $acfClasses[] = $acfFile;
            }
        }
        return $acfClasses;
    }
}
