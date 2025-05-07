<?php
/**
 * Awesome Options Framework Class
 *
 * A collection of utility methods for use throughout the Awesome Options Framework.
 *
 * @package Awesome_Options_Framework
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Awesome_Options_Framework' ) ) {
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
							'',
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
					case 'spacing':
					case 'margin':
					case 'padding':
					case 'border':
						$output[ $id ] = [];
					
						if ( isset( $input[ $id ] ) && is_array( $input[ $id ] ) ) {
							foreach ( $field['options'] as $side ) {
								$value = $input[ $id ][ $side ] ?? '';
								$output[ $id ][ $side ] = sanitize_text_field( $value );
							}
							// Also sanitize unit
							$allowed_units = [ 'px', '%', 'em', 'rem', 'vh', 'vw', 'pt' ];
							$unit = $input[ $id ]['unit'] ?? 'px';
							$output[ $id ]['unit'] = in_array( $unit, $allowed_units, true ) ? sanitize_text_field( $unit ) : 'px';
						}
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
			$type    = $field['type'] ?? 'text';
			$label   = $field['label'] ?? '';
			$desc    = $field['description'] ?? '';
		
			echo "<div class='aof-field-wrap aof-type-{$type}'>";
		
			// Label
			echo '<div class="aof-field-label">';
			if ( $label ) {
				echo "<label for='{$field['id']}' class='aof-label'>" . esc_html__( $label, 'aof' ) . "</label>";
			}
			// Description
			if ( $desc ) {
				echo '<p class="aof-description">' . esc_html__( $desc, 'aof' ) . '</p>';
			}
			echo '</div>';
			echo '<div class="aof-field-inner">';
			// Input switch
			switch ( $type ) {
				case 'text':
				case 'email':
					echo "<input type='{$type}' id='{$field['id']}' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' class='regular-text'>";
					break;
		
				case 'checkbox':
					echo "<input type='checkbox' id='{$field['id']}' name='{$this->option_name}[{$field['id']}]' value='1' " . checked( $value, 1, false ) . ">";
					break;
		
				case 'number':
					echo "<input type='number' id='{$field['id']}' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' min='" . esc_attr( $field['min'] ) . "' max='" . esc_attr( $field['max'] ) . "'>";
					break;
		
				case 'select':
					echo "<select id='{$field['id']}' name='{$this->option_name}[{$field['id']}]'>";
					foreach ( $field['options'] as $key => $label ) {
						echo "<option value='" . esc_attr( $key ) . "' " . selected( $value, $key, false ) . ">" . esc_html__( $label, 'aof' ) . "</option>";
					}
					echo "</select>";
					break;
		
				case 'color':
					echo "<input type='text' class='color-picker' id='{$field['id']}' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $value ) . "' />";
					break;
		
				case 'textarea':
					echo "<textarea id='{$field['id']}' name='{$this->option_name}[{$field['id']}]' rows='5' class='large-text'>" . esc_textarea( $value ) . "</textarea>";
					break;
		
				case 'radio':
					foreach ( $field['options'] as $key => $label ) {
						echo "<label class='aof-radio'><input type='radio' name='{$this->option_name}[{$field['id']}]' value='" . esc_attr( $key ) . "' " . checked( $value, $key, false ) . "> " . esc_html__( $label, 'aof' ) . "</label><br>";
					}
					break;
		
				case 'spacing':
				case 'margin':
				case 'padding':
				case 'border':
					$sides = $field['options'] ?? [ 'top', 'right', 'bottom', 'left' ];
					$saved = is_array( $value ) ? $value : [];
					$unit  = $saved['unit'] ?? 'px';
					$units = [ 'px', '%', 'em', 'rem', 'vh', 'vw', 'pt' ];
		
					echo "<div class='aof-spacing-wrapper'>";
					foreach ( $sides as $side ) {
						$v = isset( $saved[ $side ] ) ? esc_attr( $saved[ $side ] ) : '';
						echo "<div class='aof-spacing-input'>
							<label>" . ucfirst( $side ) . "</label>
							<input type='text' name='{$this->option_name}[{$field['id']}][{$side}]' value='{$v}' />
						</div>";
					}
					echo "<div class='aof-spacing-unit'>
							<label>" . __( 'Unit', 'aof' ) . "</label>
							<select name='{$this->option_name}[{$field['id']}][unit]'>";
					foreach ( $units as $u ) {
						echo "<option value='{$u}' " . selected( $unit, $u, false ) . ">{$u}</option>";
					}
					echo "</select></div></div>";
					break;
			}
		
			echo "</div></div>"; // .aof-field-inner
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
		
			// Important for saving settings
			settings_fields( $this->option_name );
		
			if ( ! empty( $this->sections ) ) {
		
				// Tabs (top or left)
				echo '<div class="aof-tabs">';
				foreach ( $this->sections as $key => $section ) {
					echo '<div class="aof-tab" data-tab="tab_' . esc_attr( $key ) . '">' . esc_html__( $section['label'], 'aof' ) . '</div>';
				}
				echo '</div>';
		
				// Tab content
				echo '<div class="aof-tab-content-area">';
				foreach ( $this->sections as $key => $section ) {
					echo '<div id="tab_' . esc_attr( $key ) . '" class="aof-tab-content">';
					if ( ! empty( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							echo '<div class="aof-field">';
							$this->render_field( $field );
							echo '</div>';
						}
					}
					echo '</div>';
				}
				echo '</div>'; // end .aof-tab-content-area
		
			} else {
				// Fallback: No sections, just flat fields
				foreach ( $this->fields as $field ) {
					echo '<div class="aof-field">';
					$this->render_field( $field );
					echo '</div>';
				}
			}
		
			echo '</div>'; // end .aof-form-inner
			submit_button();
			echo '</form></div></div>';
		}	
	}
}
