<div class="space-builder"
     style="
    @php
$mobileGroup = get_sub_field('space_group_mobile') ?: ['mobile_space' => '80px'];
        $tabletGroup = get_sub_field('space_group_tablet') ?: ['tablet_space' => '128px'];
        $laptopGroup = get_sub_field('space_group_laptop') ?: ['laptop_space' => '128px'];
        $desktopGroup = get_sub_field('space_group_desktop') ?: ['desktop_space' => '128px'];

        $mobileSpace = isset($mobileGroup['mobile_space']) && $mobileGroup['mobile_space'] == 'custom'
            ? ($mobileGroup['mobile_space_custom'] ?? '32px')
            : ($mobileGroup['mobile_space'] ?? '32px');

        $tabletSpace = isset($tabletGroup['tablet_space']) && $tabletGroup['tablet_space'] == 'custom'
            ? ($tabletGroup['tablet_space_custom'] ?? '32px')
            : ($tabletGroup['tablet_space'] ?? '32px');

        $laptopSpace = isset($laptopGroup['laptop_space']) && $laptopGroup['laptop_space'] == 'custom'
            ? ($laptopGroup['laptop_space_custom'] ?? '32px')
            : ($laptopGroup['laptop_space'] ?? '32px');

        $desktopSpace = isset($desktopGroup['desktop_space']) && $desktopGroup['desktop_space'] == 'custom'
            ? ($desktopGroup['desktop_space_custom'] ?? '32px')
            : ($desktopGroup['desktop_space'] ?? '32px');

        $spaceColor = preg_replace('/[^a-z0-9-]/', '', get_sub_field('space_color') ?: 'none'); @endphp

    --space-color: {{ $spaceColor === 'none' ? 'transparent' : "var(--color-{$spaceColor}, transparent)" }};
    --mobile-space: {{ $mobileSpace }};
    --tablet-space: {{ $tabletSpace }};
    --laptop-space: {{ $laptopSpace }};
    --desktop-space: {{ $desktopSpace }};
">
</div>
