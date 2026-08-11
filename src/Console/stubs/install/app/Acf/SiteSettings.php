<?php

namespace App\Acf;

use Uldin\Radicle\Support\Acf;

class SiteSettings extends Acf
{
    /**
     * ACF key.
     */
    protected $key = 'site_settings';

    /**
     * ACF title.
     */
    protected $title = 'Sitegegevens';

    /**
     * ACF fields.
     */
    public function fields()
    {
        return [
            [
                'label' => 'Kleuren',
                'name' => 'colors_tab',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'label' => 'Primaire kleur',
                'name' => 'site_primary_color',
                'type' => 'color_picker',
                'default_value' => '#ff6347',
                'return_format' => 'string',
                'wrapper' => ['width' => '25'],
            ],
            [
                'label' => 'Tekst op primair',
                'name' => 'site_primary_text_color',
                'type' => 'color_picker',
                'default_value' => '#ffffff',
                'return_format' => 'string',
                'instructions' => 'Tekstkleur voor knoppen en vlakken met de primaire achtergrondkleur.',
                'wrapper' => ['width' => '25'],
            ],
            [
                'label' => 'Secundaire kleur',
                'name' => 'site_secondary_color',
                'type' => 'color_picker',
                'default_value' => '#ffb347',
                'return_format' => 'string',
                'wrapper' => ['width' => '25'],
            ],
            [
                'label' => 'Tekst op secundair',
                'name' => 'site_secondary_text_color',
                'type' => 'color_picker',
                'default_value' => '#111111',
                'return_format' => 'string',
                'instructions' => 'Tekstkleur voor knoppen en vlakken met de secundaire achtergrondkleur.',
                'wrapper' => ['width' => '25'],
            ],
            [
                'label' => 'Donkere kleur',
                'name' => 'site_dark_color',
                'type' => 'color_picker',
                'default_value' => '#111111',
                'return_format' => 'string',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Achtergrondkleur',
                'name' => 'site_background_color',
                'type' => 'color_picker',
                'default_value' => '#ffffff',
                'return_format' => 'string',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Oppervlaktekleur',
                'name' => 'site_surface_color',
                'type' => 'color_picker',
                'default_value' => '#ffffff',
                'return_format' => 'string',
                'instructions' => 'Voor onder andere kaarten en lichte knoppen.',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Tekstkleur',
                'name' => 'site_text_color',
                'type' => 'color_picker',
                'default_value' => '#111111',
                'return_format' => 'string',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Afronding componenten',
                'name' => 'site_border_radius',
                'type' => 'range',
                'instructions' => 'Globale hoekafronding voor knoppen en andere componenten.',
                'default_value' => 6,
                'min' => 0,
                'max' => 32,
                'step' => 1,
                'append' => 'px',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Maximale containerbreedte',
                'name' => 'site_container_width',
                'type' => 'range',
                'instructions' => 'Globale maximale breedte voor alle containers op de website. Volledig brede onderdelen blijven hiervan uitgezonderd.',
                'default_value' => 1248,
                'min' => 960,
                'max' => 1600,
                'step' => 8,
                'append' => 'px',
                'wrapper' => ['width' => '33'],
            ],
            [
                'label' => 'Algemeen',
                'name' => 'general_tab',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'label' => 'Logo',
                'name' => 'site_logo',
                'type' => 'image',
                'instructions' => 'Upload het logo dat op de website gebruikt kan worden.',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'library' => 'all',
            ],
            [
                'label' => 'Header',
                'name' => 'header_tab',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'label' => 'Headerknop tonen',
                'name' => 'header_button_enabled',
                'type' => 'true_false',
                'default_value' => 1,
                'ui' => 1,
                'ui_on_text' => 'Tonen',
                'ui_off_text' => 'Verbergen',
            ],
            [
                'label' => 'Mobiel menu',
                'name' => 'header_mobile_menu_style',
                'type' => 'select',
                'choices' => [
                    'fullscreen' => 'Volledig scherm',
                    'dropdown' => 'Compacte dropdown',
                ],
                'default_value' => 'fullscreen',
                'return_format' => 'value',
                'ui' => 1,
                'instructions' => 'Het volledige scherm begint onder de header, zodat logo en menuknop zichtbaar blijven.',
            ],
            [
                'label' => 'Headerknop',
                'name' => 'header_button_link',
                'type' => 'link',
                'instructions' => 'Stel de tekst, link en eventueel een nieuw tabblad in.',
                'return_format' => 'array',
                'wrapper' => ['width' => '66'],
                'conditional_logic' => [
                    [
                        [
                            'field' => 'header_button_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Kleur van headerknop',
                'name' => 'header_button_variant',
                'type' => 'select',
                'choices' => [
                    'default' => 'Primair',
                    'secondary' => 'Secundair',
                    'outline' => 'Omlijnd',
                ],
                'default_value' => 'default',
                'return_format' => 'value',
                'ui' => 1,
                'wrapper' => ['width' => '34'],
                'conditional_logic' => [
                    [
                        [
                            'field' => 'header_button_enabled',
                            'operator' => '==',
                            'value' => '1',
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Contactgegevens',
                'name' => 'contact_tab',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'label' => 'E-mailadres',
                'name' => 'contact_email',
                'type' => 'email',
            ],
            [
                'label' => 'Telefoonnummer',
                'name' => 'contact_phone',
                'type' => 'text',
            ],
            [
                'label' => 'Adres',
                'name' => 'contact_address',
                'type' => 'text',
            ],
            [
                'label' => 'Postcode',
                'name' => 'contact_postcode',
                'type' => 'text',
                'wrapper' => [
                    'width' => '25',
                ],
            ],
            [
                'label' => 'Plaats',
                'name' => 'contact_city',
                'type' => 'text',
                'wrapper' => [
                    'width' => '75',
                ],
            ],
            [
                'label' => 'Footer',
                'name' => 'footer_tab',
                'type' => 'tab',
                'placement' => 'top',
            ],
            [
                'label' => 'Footerbeschrijving',
                'name' => 'footer_description',
                'type' => 'textarea',
                'instructions' => 'Optionele korte tekst onder het logo of de sitenaam.',
                'rows' => 3,
                'new_lines' => 'br',
            ],
            [
                'label' => 'Onderregel',
                'name' => 'footer_bottom_text',
                'type' => 'text',
                'instructions' => 'Optionele tekst naast het copyright, bijvoorbeeld een bedrijfs- of ontwikkelaarsvermelding.',
            ],
            [
                'label' => 'Social media',
                'name' => 'social_links',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Social media toevoegen',
                'sub_fields' => [
                    [
                        'label' => 'Platform',
                        'name' => 'platform',
                        'type' => 'select',
                        'required' => 1,
                        'choices' => [
                            'instagram' => 'Instagram',
                            'facebook' => 'Facebook',
                            'youtube' => 'YouTube',
                            'tiktok' => 'TikTok',
                            'other' => 'Anders',
                        ],
                    ],
                    [
                        'label' => 'Naam',
                        'name' => 'label',
                        'type' => 'text',
                        'instructions' => 'Alleen nodig als je bij Platform voor Anders kiest.',
                    ],
                    [
                        'label' => 'URL',
                        'name' => 'url',
                        'type' => 'url',
                        'required' => 1,
                    ],
                ],
            ],
            [
                'label' => 'Volgorde dienstenpagina',
                'name' => 'services_archive_order',
                'type' => 'relationship',
                'post_type' => ['dienst'],
                'post_status' => ['publish'],
                'filters' => ['search'],
                'return_format' => 'id',
                'instructions' => 'Selecteer de diensten en versleep ze rechts in de gewenste volgorde voor /diensten/. Niet-geselecteerde diensten worden er automatisch achter geplaatst.',
            ],
        ];
    }

    /**
     * Show this field group on the Sitegegevens options page.
     */
    public function location()
    {
        return [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'site-settings',
                ],
            ],
        ];
    }
}
