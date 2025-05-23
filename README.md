\=== Awesome Options Framework === 
- Contributors: roymahfooz 
- Tags: options framework, settings, WordPress options, admin panel 
- Requires at least: 5.0 Tested up to: 6.4 
- Requires PHP: 7.4 Stable tag: 1.0.0 License: GPLv2 or later License URI: [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html) 
- Text Domain: aof

A dynamic and powerful options framework for WordPress that allows developers to create flexible settings pages with ease.

\== Description ==

Awesome Options Framework is a lightweight yet powerful WordPress options framework that enables developers to create dynamic admin settings pages quickly. It supports multiple field types and ensures secure data handling with WordPress standards.

**Features:**

- Dynamically generates settings pages from an array of fields.
- Supports text, checkbox, number, and select fields.
- Follows WordPress security best practices with sanitization and escaping.
- Fully translation-ready (Text Domain: aof).
- Beautifully styled admin settings page.

\== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/awesome-options-framework/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Modify the settings in your code to define your custom options.

\== Usage ==

To use the framework, initialize it with an array of fields in your plugin:

```php
// Example Usage
new Awesome_Options_Framework([
    'option_name' => 'my_plugin_settings',
    'page_title'  => __('My Plugin Settings', 'aof'),
    'menu_slug'   => 'my-plugin-settings',
    'menu_icon'   => 'dashicons-admin-generic',
    'fields'      => [
        [
            'id' => 'custom_text',
            'type' => 'text',
            'label' => __('Custom Text', 'aof'),
            'default' => '',
        ],
        [
            'id' => 'enable_feature',
            'type' => 'checkbox',
            'label' => __('Enable Feature', 'aof'),
            'default' => 0,
        ],
        [
            'id' => 'background_color',
            'type' => 'color',
            'label' => __('Background Color', 'aof'),
            'default' => '#ffffff',
        ],
    ]
] );
```

```php
// Example Usage with sections/tabs
new Awesome_Options_Framework( [
    'option_name' => 'my_plugin_settings',
    'page_title'  => __('My Plugin Settings', 'aof'),
    'menu_slug'   => 'my-plugin-settings',
    'menu_icon'   => 'dashicons-admin-tools',
    'tab_layout'  => 'horizontal', // or 'vertical'
    'sections'    => [
        'general' => [
            'label' => __('General Settings', 'aof'),
            'fields' => [
                [
                    'id'      => 'site_title',
                    'type'    => 'text',
                    'label'   => __('Site Title', 'aof'),
                    'default' => '',
                ],
                [
                    'id'      => 'background_color',
                    'type'    => 'color',
                    'label'   => __('Background Color', 'aof'),
                    'default' => '#ffffff',
                ],
                [
                    'id'      => 'enable_feature',
                    'type'    => 'checkbox',
                    'label'   => __('Enable Feature', 'aof'),
                    'default' => 0,
                ],
            ],
        ],
        'advanced' => [
            'label' => __('Advanced Settings', 'aof'),
            'fields' => [
                [
                    'id'      => 'max_items',
                    'type'    => 'number',
                    'label'   => __('Maximum Items', 'aof'),
                    'default' => 10,
                    'min'     => 1,
                    'max'     => 100,
                ],
                [
                    'id'      => 'email',
                    'type'    => 'email',
                    'label'   => __('Admin Email', 'aof'),
                    'default' => '',
                ],
                [
                    'id'      => 'mode',
                    'type'    => 'select',
                    'label'   => __('Mode', 'aof'),
                    'default' => 'simple',
                    'options' => [
                        'simple'  => __('Simple', 'aof'),
                        'advanced'=> __('Advanced', 'aof'),
                    ],
                ],
            ],
        ],
    ]
] );

```

\== Frequently Asked Questions ==

\= Can I add more field types? = Yes! The framework is built to be easily extendable. You can add more field types by modifying the `render_field()` function.

\= Is this plugin translation-ready? = Yes! It includes a text domain (`aof`) and follows WordPress translation standards.

\== Changelog ==

\= 1.0.0 =

- Initial release with core functionality.

\== Upgrade Notice ==

\= 1.0.0 = Initial release. No upgrade needed.

