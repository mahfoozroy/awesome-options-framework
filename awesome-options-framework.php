<?php
/**
 * Plugin Name: Awesome Options Framework
 * Description: A dynamic WordPress options framework.
 * Version: 1.0.0
 * Author: Roy Mahfooz
 * Author URI: https://roymahfooz.com
 * Text Domain: aof
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Awesome_Options_Framework {
    private $option_name;
    private $page_title;
    private $menu_slug;
    private $fields;

    public function __construct($args) {
        $this->option_name = $args['option_name'] ?? 'awesome_options_framework_settings';
        $this->page_title = $args['page_title'] ?? __('Awesome Options Framework', 'aof');
        $this->menu_slug = $args['menu_slug'] ?? 'awesome-options-framework';
        $this->fields = $args['fields'] ?? [];

        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }

    public function load_textdomain() {
        load_plugin_textdomain('aof', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_settings_page() {
        add_options_page(
            esc_html($this->page_title),
            esc_html($this->page_title),
            'manage_options',
            esc_attr($this->menu_slug),
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting($this->option_name, $this->option_name, [$this, 'sanitize_settings']);

        add_settings_section(
            'general_settings',
            esc_html__('General Settings', 'aof'),
            null,
            $this->option_name
        );

        foreach ($this->fields as $field) {
            add_settings_field(
                esc_attr($field['id']),
                esc_html__($field['label'], 'aof'),
                [$this, 'render_field'],
                $this->option_name,
                'general_settings',
                $field
            );
        }
    }

    public function sanitize_settings($input) {
        $output = [];
        foreach ($this->fields as $field) {
            $id = $field['id'];
            if ($field['type'] === 'checkbox') {
                $output[$id] = isset($input[$id]) ? 1 : 0;
            } elseif ($field['type'] === 'number') {
                $output[$id] = isset($input[$id]) ? max($field['min'], min($field['max'], intval($input[$id]))) : $field['default'];
            } elseif ($field['type'] === 'select' && isset($field['options'][$input[$id]])) {
                $output[$id] = sanitize_text_field($input[$id]);
            } else {
                $output[$id] = sanitize_text_field($input[$id] ?? $field['default']);
            }
        }
        return $output;
    }

    public function render_field($field) {
        $options = get_option($this->option_name);
        $value = isset($options[$field['id']]) ? esc_attr($options[$field['id']]) : esc_attr($field['default']);
        
        switch ($field['type']) {
            case 'text':
                echo "<input type='text' name='{$this->option_name}[{$field['id']}]' value='$value' class='regular-text'>";
                break;
            case 'checkbox':
                $checked = checked($value, 1, false);
                echo "<input type='checkbox' name='{$this->option_name}[{$field['id']}]' value='1' $checked>";
                break;
            case 'number':
                echo "<input type='number' name='{$this->option_name}[{$field['id']}]' value='$value' min='".esc_attr($field['min'])."' max='".esc_attr($field['max'])."'>";
                break;
            case 'select':
                echo "<select name='{$this->option_name}[{$field['id']}]'>";
                foreach ($field['options'] as $key => $label) {
                    $selected = selected($value, $key, false);
                    echo "<option value='".esc_attr($key)."' $selected>".esc_html__($label, 'aof')."</option>";
                }
                echo "</select>";
                break;
        }
    }

    public function enqueue_styles() {
        echo '<style>
            .wrap { max-width: 800px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { font-size: 24px; font-weight: bold; color: #333; }
            input[type="text"], input[type="number"], select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; }
            input[type="checkbox"] { transform: scale(1.2); margin-top: 10px; }
            input[type="submit"] { background: #0073aa; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
            input[type="submit"]:hover { background: #005177; }
        </style>';
    }

    public function render_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->page_title) . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields($this->option_name);
        do_settings_sections($this->option_name);
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
