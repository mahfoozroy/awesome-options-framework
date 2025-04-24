<?php
/**
 * Awesome Options Framework Class
 *
 * A collection of utility methods for use throughout the Awesome Options Framework.
 *
 * @package Awesome_Options_Framework
 */
class Awesome_Options_Framework {
	private $option_name;
	private $page_title;
	private $menu_slug;
	private $menu_icon;
	private $fields;
	private $sections;
	private $tab_layout;

	/**
	 * Constructor
	 *
	 * @param array $args Plugin arguments.
	 */
	public function __construct( $args ) {
		$this->option_name = $args['option_name'] ?? 'awesome_options_framework_settings';
		$this->page_title  = $args['page_title'] ?? __( 'Awesome Options Framework', 'aof' );
		$this->menu_slug   = $args['menu_slug'] ?? 'awesome-options-framework';
		$this->menu_icon   = $args['menu_icon'] ?? 'dashicons-admin-generic';
		$this->fields      = $args['fields'] ?? [];
		$this->sections    = $args['sections'] ?? [];
		$this->tab_layout  = $args['tab_layout'] ?? 'horizontal';

		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'aof', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Add top-level admin menu.
	 */
	public function add_settings_page() {
		add_menu_page(
			esc_html( $this->page_title ),
			esc_html( $this->page_title ),
			'manage_options',
			esc_attr( $this->menu_slug ),
			[ $this, 'render_settings_page' ],
			esc_attr( $this->menu_icon ),
			25
		);
	}

	/**
	 * Register settings and fields.
	 */
	public function register_settings() {
		register_setting( $this->option_name, $this->option_name, [ $this, 'sanitize_settings' ] );

		if ( ! empty( $this->sections ) ) {
			foreach ( $this->sections as $key => $section ) {
				add_settings_section(
					$key,
					esc_html__( $section['label'], 'aof' ),
					null,
					$this->option_name . '_' . $key
				);

				foreach ( $section['fields'] as $field ) {
					add_settings_field(
						esc_attr( $field['id'] ),
						esc_html__( $field['label'], 'aof' ),
						[ $this, 'render_field' ],
						$this->option_name . '_' . $key,
						$key,
						$field
					);
				}
			}
		} else {
			add_settings_section( 'awesome_options', '', null, $this->option_name );
			foreach ( $this->fields as $field ) {
				add_settings_field(
					esc_attr( $field['id'] ),
					esc_html__( $field['label'], 'aof' ),
					[ $this, 'render_field' ],
					$this->option_name,
					'awesome_options',
					$field
				);
			}
		}
	}

	/**
	 * Sanitize settings on save.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$output = [];

		$fields = ! empty( $this->sections )
			? array_merge( ...array_column( $this->sections, 'fields' ) )
			: $this->fields;

		foreach ( $fields as $field ) {
			$id    = $field['id'];
			$type  = $field['type'];
			$value = $input[ $id ] ?? $field['default'];

			switch ( $type ) {
				case 'checkbox':
					$output[ $id ] = isset( $input[ $id ] ) ? 1 : 0;
					break;
				case 'number':
					$output[ $id ] = max( $field['min'], min( $field['max'], intval( $value ) ) );
					break;
				case 'select':
					$output[ $id ] = isset( $field['options'][ $value ] ) ? sanitize_text_field( $value ) : '';
					break;
				case 'email':
					$output[ $id ] = sanitize_email( $value );
					break;
				case 'color':
					$output[ $id ] = sanitize_hex_color( $value );
					break;
				default:
					$output[ $id ] = sanitize_text_field( $value );
					break;
			}
		}

		return $output;
	}

	/**
	 * Render individual field.
	 *
	 * @param array $field Field data.
	 */
	public function render_field( $field ) {
		$options = get_option( $this->option_name );
		$value   = $options[ $field['id'] ] ?? $field['default'];

		switch ( $field['type'] ) {
			case 'text':
			case 'email':
				echo "<input type='{$field['type']}' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' class='regular-text'>";
				break;

			case 'checkbox':
				echo "<input type='checkbox' name='{$this->option_name}[{$field['id']}]' value='1' " . checked( $value, 1, false ) . ">";
				break;

			case 'number':
				echo "<input type='number' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' min='" . esc_attr( $field['min'] ) . "' max='" . esc_attr( $field['max'] ) . "'>";
				break;

			case 'select':
				echo "<select name='{$this->option_name}[{$field['id']}]'>";
				foreach ( $field['options'] as $key => $label ) {
					echo "<option value='" . esc_attr( $key ) . "' " . selected( $value, $key, false ) . ">" . esc_html__( $label, 'aof' ) . "</option>";
				}
				echo "</select>";
				break;

			case 'color':
				echo "<input type='text' class='color-picker' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' />";
				break;

			case 'textarea':
				echo "<textarea name='{$this->option_name}[{$field['id']}]' rows='5' class='large-text'>" . esc_textarea( $value ) . "</textarea>";
				break;

			case 'radio':
				foreach ( $field['options'] as $key => $label ) {
					echo "<label><input type='radio' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $key ) . "' " . checked( $value, $key, false ) . "> " . esc_html__( $label, 'aof' ) . "</label><br>";
				}
				break;
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'toplevel_page_' . $this->menu_slug ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'aof-js', AOF_URL . 'assets/options.js', [ 'wp-color-picker', 'jquery' ], null, true );
		wp_enqueue_style( 'aof-css', AOF_URL . 'assets/options.css' );
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		echo '<div id="aof-app" class="aof-wrap ' . esc_attr( $this->tab_layout ) . '">';
		echo '<div class="aof-inner-wrap">';
		echo '<h1>' . esc_html( $this->page_title ) . '</h1>';
		echo '<form method="post" action="options.php">';
		echo '<div class="aof-form-inner-' . esc_attr( $this->tab_layout ) . '">';

		settings_fields( $this->option_name );

		if ( ! empty( $this->sections ) ) {
			// Tabs
			echo '<div class="aof-tabs">';
			foreach ( $this->sections as $key => $section ) {
				echo '<div class="aof-tab" data-tab="tab_' . esc_attr( $key ) . '">' . esc_html__( $section['label'], 'aof' ) . '</div>';
			}
			echo '</div>';

			// Tab content
			echo '<div class="aof-tab-content-area">';
			foreach ( $this->sections as $key => $section ) {
				echo '<div id="tab_' . esc_attr( $key ) . '" class="aof-tab-content">';
				do_settings_sections( $this->option_name . '_' . $key );
				echo '</div>';
			}
			echo '</div>';
		} else {
			do_settings_sections( $this->option_name );
		}
		echo '</div>';
		submit_button();
		echo '</form></div></div>';
	}
}
