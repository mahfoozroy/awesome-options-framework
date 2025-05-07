<?php
/**
 * AOF Helper Class
 *
 * A collection of utility methods for use throughout the Awesome Options Framework.
 *
 * @package Awesome_Options_Framework
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AOF_Helper' ) ) {
	class AOF_Helper {

		/**
		 * Ensure a CSS unit value is properly formatted for fields like padding, margins etc.
		 *
		 * @param string|int|float $value        The value to sanitize (e.g. "20", "20px", "1.5em").
		 * @param string           $default_unit The default unit to append (e.g. "px", "em").
		 *
		 * @return string Sanitized unit value (e.g. "20px").
		 */
		public static function sanitize_unit_value( $value, $default_unit = 'px' ) {
			if ( is_numeric( $value ) ) {
				return $value . $default_unit;
			}

			if ( preg_match( '/^[0-9.]+(px|em|rem|%|vh|vw|pt)$/', trim( $value ) ) ) {
				return $value;
			}

			$numeric = floatval( $value );
			return $numeric . $default_unit;
		}

		/**
		 * Validate a hex color string.
		 *
		 * @param string $value The color value (e.g. "#fff", "#ffffff").
		 *
		 * @return bool True if valid hex color, false otherwise.
		 */
		public static function is_valid_color( $value ) {
			return (bool) preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value );
		}

		/**
		 * Check if an array is associative.
		 *
		 * @param array $array Input array.
		 *
		 * @return bool True if associative, false if indexed.
		 */
		public static function is_associative_array( $array ) {
			return is_array( $array ) && array_keys( $array ) !== range( 0, count( $array ) - 1 );
		}

		/**
		 * Get a specific field value from a registered options array.
		 *
		 * @param string $option_name The registered option name.
		 * @param string $key         The key inside the options array.
		 * @param mixed  $fallback    Fallback value if key is not set.
		 *
		 * @return mixed The value or fallback.
		 */
		public static function get_option_field( $option_name, $key, $fallback = null ) {
			$options = get_option( $option_name, [] );
			return isset( $options[ $key ] ) ? $options[ $key ] : $fallback;
		}

		/**
		 * Merge default values with saved option values.
		 *
		 * Useful when rendering fields to ensure all defaults exist in the final array.
		 *
		 * @param array $fields Field definitions (must have 'id' and optionally 'default').
		 * @param array $saved  Saved option array from get_option().
		 *
		 * @return array Merged field values.
		 */
		public static function merge_defaults( $fields, $saved ) {
			$defaults = [];

			foreach ( $fields as $field ) {
				$defaults[ $field['id'] ] = $field['default'] ?? null;
			}

			return array_merge( $defaults, (array) $saved );
		}
		
	}
}
