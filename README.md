# Radicle Package

This repo can be used to scaffold an Acorn package. See the [Acorn Package Development](https://roots.io/acorn/docs/package-development/) docs for further information.

## Installation

You can install this package with Composer:

```bash
composer require uldin/radicle
```

Install the complete project blueprint (ACF site settings, home template, Space
component, styles and application layout) with:

```shell
wp acorn radicle:install
```

The command is idempotent and skips existing files whose contents differ. After
reviewing those conflicts, overwrite them explicitly with:

```shell
wp acorn radicle:install --force
```

## Migrating existing installations

After updating an existing installation, migrate users from the legacy customer
role to the Uldin role with:

```shell
wp acorn uldin:migrate
```

The command is idempotent and keeps the legacy role for backwards compatibility.
Once no older code depends on that role, it can also be removed:

```shell
wp acorn uldin:migrate --remove-legacy-role
```

You can publish the config file with:

Flare config file

```shell
$ wp acorn vendor:publish --provider="Uldin\Radicle\Providers\FlareServiceProvider"
```

ACF config file

```shell
$ wp acorn vendor:publish --provider="Uldin\Radicle\Providers\AcfServiceProvider"
```


## ACF component blocks

When `App\Classes\AcfComponentClass` exists, public static methods ending in
`Layout` are registered as ACF blocks for the WordPress block editor. Radicle
does not force the block editor, so WordPress or the Classic Editor plugin can
keep deciding which editor is used. A layout named `hero` renders
`resources/views/partials/hero.blade.php`.

To explicitly force the block editor for the configured post types, set
`acf.component_blocks.force_block_editor` to `true`.

The existing Flexible Content layout format can be used unchanged:

```php
public static function heroLayout()
{
    return [
        'name' => 'hero',
        'label' => 'Hero',
        'sub_fields' => [
            // ACF fields...
        ],
    ];
}
```

Block templates receive every ACF field as a variable, as well as `$fields`,
`$block`, `$content`, `$isPreview`, and `$postId`. A custom partial can be set
with `'view' => 'partials.custom-hero'` on a layout.

Existing Flexible Content partials may keep using `get_sub_field()` and
`get_row_index()`. Radicle creates a temporary ACF row context while rendering
the partial as a block. Repeater and group values are mapped automatically.

Gutenberg blocks store their own ACF data by default, enabling live previews and
field editing for newly inserted blocks. Existing Flexible Content field groups
remain available in the Classic Editor, but are hidden in the Block Editor to
avoid showing two independent component editors. Set
`acf.component_blocks.hide_flexible_content_in_block_editor` to `false` to show
them in both editors. Use the optional `flexible_content` data source only for
save-and-refresh previews generated from Flexible Content.

When block data is used, Radicle synchronizes component blocks back to the
configured Flexible Content field after a Gutenberg save. Existing templates
and the Classic Editor can therefore continue reading the same component rows.
After a Classic Editor save, Radicle also rebuilds the Gutenberg blocks from
those Flexible Content rows so both editors reopen with the latest values.

When an existing Flexible Content page with empty `post_content` is first opened
in the Block Editor, Radicle automatically creates full ACF blocks and stores a
rollback copy in `_radicle_21_block_migration_backup`. Disable this behavior with
`acf.component_blocks.auto_migrate_legacy_content`.

The project's `resources/css/app.css` entry point is loaded in the block editor
by default, so component previews use the frontend utilities. This can be
changed or disabled with `acf.component_blocks.editor_style`.

Frontend reveal animations are disabled in ACF block previews by default, so
content that starts with `opacity-0` remains visible while editing. The frontend
animation remains unchanged. Set `acf.component_blocks.disable_editor_motion`
to `false` when animations should also run inside the editor preview.

Site colors and border radius are read from the configured ACF option fields and
injected as CSS custom properties in the editor preview. Configure mappings with
`acf.component_blocks.editor_css_variables`. Plain preview links use the primary
color by default; configure this with `acf.component_blocks.editor_link_color`.
When a mapped option field does not exist, Radicle injects no value for it and
the project's existing Tailwind theme remains in control.

The block inserter is restricted to application ACF components by default on
the post types configured in `acf.component_blocks.post_types`. Editors for
other post types, such as JetFormBuilder forms, remain unrestricted. Add
intentional exceptions through `acf.component_blocks.additional_allowed_blocks`,
or disable the restriction with `acf.component_blocks.restrict_block_types`.
