<?php

namespace App\Classes;

class AcfComponentClass
{
    public static function spaceLayout()
    {
        $spaceLayout = [
            'name' => 'space',
            'label' => 'Space',
            'display' => 'block',
            'sub_fields' => [
                [
                    'label' => 'Kleur',
                    'name' => 'space_color',
                    'type' => 'select',
                    'instructions' => 'Gebruik een van de centrale kleuren uit Sitegegevens.',
                    'choices' => [
                        'none' => 'Geen kleur',
                        'yellow' => 'Yellow',
                        'blue' => 'Blue',
                        'red' => 'Red'
                    ],
                    'default_value' => 'none',
                    'return_format' => 'value',
                    'ui' => 1,
                ],
            ],
        ];

        $spaceChoices = ['0px' => '0px'];

        for ($i = 8; $i <= 360; $i += 8) {
            $px = $i . 'px';
            $spaceChoices[$px] = $px;
        }

        $deviceGroups = [
            'mobile' => 'Mobile',
            'tablet' => 'Tablet',
            'laptop' => 'Laptop',
            'desktop' => 'Desktop',
        ];

        foreach ($deviceGroups as $device => $label) {
            $spaceLayout['sub_fields'][] = [
                'type' => 'group',
                'name' => 'space_group_' . $device,
                'wrapper' => ['width' => '25%'],
                'sub_fields' => [
                    [
                        'label' => $label . ' Space',
                        'name' => $device . '_space',
                        'type' => 'select',
                        'default_value' => '32px',
                        'choices' => $spaceChoices,
                    ],
                ],
            ];
        }
        return $spaceLayout;
    }
}
