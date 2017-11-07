<?php
/**
 * Plugin Name:     WP Multilang
 * Plugin URI:      https://github.com/VaLeXaR/wp-multilang
 * Description:     Multilingual plugin for WordPress
 * Author:          Valentyn Riaboshtan
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wp-multilang
 * Domain Path:     /languages
 * Version:         2.0.0
 *
 * @package  WPM
 * @category Core
 * @author   Valentyn Riaboshtan
 */

use WPM\Includes\WP_Multilang;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Class autoloader.
 */
include_once( __DIR__ . '/includes/autoload.php' );

// Define WPM_PLUGIN_FILE.
if ( ! defined( 'WPM_PLUGIN_FILE' ) ) {
	define( 'WPM_PLUGIN_FILE', __FILE__ );
}

function wpm() {
	return WP_Multilang::instance();
}

wpm();
