<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ACF
    |--------------------------------------------------------------------------
    |
    | Advanced Custom Fields is a great plugin for adding custom fields to
    | WordPress. You can find out more about ACF at the following URL:
    | https://www.advancedcustomfields.com
    |
    */

    /**
     * Add options pages
     */
    'options_pages' => [],

    /*
    |--------------------------------------------------------------------------
    | Component blocks
    |--------------------------------------------------------------------------
    |
    | Turn the layouts from App\Classes\AcfComponentClass into ACF blocks for
    | the WordPress block editor. Each layout is rendered by a Blade partial.
    |
    */
    'component_blocks' => [
        'enabled' => true,
        'force_block_editor' => false,
        'post_types' => ['page'],
        'data_source' => 'block',
        'flexible_content_field' => 'content',
        'auto_migrate_legacy_content' => true,
        'hide_flexible_content_in_block_editor' => true,
        'class' => App\Classes\AcfComponentClass::class,
        'view_path' => 'partials',
        'editor_style' => 'resources/css/app.css',
        'disable_editor_motion' => true,
        'editor_link_color' => 'var(--color-primary)',
        'restrict_block_types' => true,
        'additional_allowed_blocks' => [],
        'editor_css_variables' => [
            '--site-primary-color' => [
                'field' => 'site_primary_color',
                'fallback' => '#ff6347',
            ],
            '--site-primary-text-color' => [
                'field' => 'site_primary_text_color',
                'fallback' => '#ffffff',
            ],
            '--site-secondary-color' => [
                'field' => 'site_secondary_color',
                'fallback' => '#ffb347',
            ],
            '--site-secondary-text-color' => [
                'field' => 'site_secondary_text_color',
                'fallback' => '#111111',
            ],
            '--site-dark-color' => [
                'field' => 'site_dark_color',
                'fallback' => '#111111',
            ],
            '--site-background-color' => [
                'field' => 'site_background_color',
                'fallback' => '#ffffff',
            ],
            '--site-surface-color' => [
                'field' => 'site_surface_color',
                'fallback' => '#ffffff',
            ],
            '--site-text-color' => [
                'field' => 'site_text_color',
                'fallback' => '#111111',
            ],
            '--site-border-radius' => [
                'field' => 'site_border_radius',
                'fallback' => 6,
                'type' => 'number',
                'suffix' => 'px',
            ],
        ],
        'category' => [
            'slug' => 'components',
            'title' => 'Components',
        ],
    ],
];
