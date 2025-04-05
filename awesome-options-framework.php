<?php
/**
 * Plugin Name: Awesome Options Framework
 * Description: A dynamic WordPress options framework.
 * Version: 1.0
 * Author: Roy Mahfooz
 * Author URI: https://roymahfooz.com
 * Text Domain: aof
 * Domain Path: /languages
 */

if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

class Awesome_Options_Framework {
    private $option_name;
    private $page_title;
    private $menu_slug;
    private $menu_icon;
    private $fields;

    public function __construct($args) {
        $this->option_name = $args['option_name'] ?? 'awesome_options_framework_settings';
        $this->page_title  = $args['page_title'] ?? __('Awesome Options Framework', 'aof');
        $this->menu_slug   = $args['menu_slug'] ?? 'awesome-options-framework';
        $this->menu_icon   = $args['menu_icon'] ?? 'dashicons-admin-generic';
        $this->fields      = $args['fields'] ?? [];

        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'plugins_loaded', [ $this, 'load_textdomain'] );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'aof', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
    }

    public function add_settings_page() {
        add_menu_page(
            esc_html( $this->page_title ),
            esc_html( $this->page_title ),
            'manage_options',
            esc_attr( $this->menu_slug ),
            [ $this, 'render_settings_page'],
            esc_attr( $this->menu_icon ),
            25
        );
    }

    public function register_settings() {
        register_setting( $this->option_name, $this->option_name, [ $this, 'sanitize_settings'] );

        add_settings_section(
            'general_settings',
            esc_html__('General Settings', 'aof'),
            null,
            $this->option_name
        );

        foreach ( $this->fields as $field ) {
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
            } elseif ($field['type'] === 'email') {
                $output[$id] = sanitize_email($input[$id] ?? '');
            } elseif ($field['type'] === 'color') {
                $output[$id] = sanitize_hex_color($input[$id] ?? $field['default']);
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
            case 'color':
                    echo "<input type='text' class='color-picker' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr($value) . "' />";
                    break;
            case 'textarea':
                echo "<textarea name='{$this->option_name}[{$field['id']}]' rows='5' class='large-text'>$value</textarea>";
                break;
            case 'radio':
                foreach ( $field['options'] as $key => $label ) {
                    $checked = checked($value, $key, false);
                    echo "<label><input type='radio' name='{$this->option_name}[{$field['id']}]' value='".esc_attr($key)."' $checked> ".esc_html__($label, 'aof')."</label><br>";
                }
                break;
        }
    }

    public function enqueue_scripts( $hook ) {
        if ( $hook !== 'toplevel_page_' . $this->menu_slug ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'aof-admin-js', plugins_url( 'assets/options.js', __FILE__), ['wp-color-picker'], false, true );
        wp_enqueue_style( 'aof-admin-css', plugins_url( 'assets/options.css', __FILE__) );
    }

    public function render_settings_page() {
        echo '<div class="aof-wrap"><div class="aof-inner-wrap">';
        echo '<h1>' . esc_html_e( $this->page_title, 'aof' ) . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( $this->option_name );
        do_settings_sections( $this->option_name );
        submit_button();
        echo '</form>';
        echo '</div></div>';
    }
}

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
]);
