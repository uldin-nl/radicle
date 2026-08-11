<?php


namespace App\Acf;

use Uldin\Radicle\Support\Acf;
use App\Classes\AcfComponentClass;
use ReflectionClass;

class TemplateHome extends Acf
{
    /**
     * ACF key
     */
    protected $key = 'template_home';
    /**
     * ACF title
     */
    protected $title = 'Main Template';

    /**
     * ACF fields
     */
    public function fields()
    {
        $componentClass = new ReflectionClass('App\Classes\AcfComponentClass');

        return [
            [
                'label' => 'Content',
                'name' => 'content',
                'type' => 'flexible_content',
                'layouts' => array_map(
                    function ($method) {
                        return $method->invoke(null);
                    },
                    array_filter($componentClass->getMethods(), function ($method) {
                        return str_ends_with($method->name, 'Layout');
                    }),
                ),
            ],
        ];
    }

    /**
     * ACF Location
     */

    public function location()
    {
        return [
            [
                [
                    'param' => 'page_template',
                    'operator' => '==',
                    'value' => 'template-home.blade.php',
                ],
            ],
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'show',
                ],
            ],
        ];
    }

    /**
     * ACF Options
     */
    public function options()
    {
        return [
            'hide_on_screen' => ['the_content'],
        ];
    }
}
