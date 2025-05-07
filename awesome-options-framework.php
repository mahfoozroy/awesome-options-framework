<?php
/**
 * Plugin Name: Awesome Options Framework
 * Description: A dynamic and extendable options framework for WordPress admin settings.
 * Version: 1.0
 * Author: Roy Mahfooz
 * Author URI: https://roymahfooz.com
 * Text Domain: aof
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin directory path
define( 'AOF_PATH', plugin_dir_path(__FILE__) );
define( 'AOF_URL', plugin_dir_url(__FILE__) );

// Include frame classes.
require_once AOF_PATH . 'framework/class-aof-helper.php';
require_once AOF_PATH . 'framework/class-awesome-options-framework.php';