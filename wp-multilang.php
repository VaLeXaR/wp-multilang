<?php
/**
 * Plugin Name:       WP Multilang
 * Plugin URI:        https://github.com/VaLeXaR/wp-multilang
 * GitHub Plugin URI: https://github.com/VaLeXaR/wp-multilang
 * Description:       Multilingual plugin for WordPress
 * Author:            Valentyn Riaboshtan
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-multilang
 * Domain Path:       /languages
 * Version:           2.4.1
 * Copyright:         © 2017-2019 Valentyn Riaboshtan
 *
 * @package  WPM
 * @category Core
 * @author   Valentyn Riaboshtan
 */

use WPM\Includes\WP_Multilang;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define WPM_PLUGIN_FILE.
if ( ! defined( 'WPM_PLUGIN_FILE' ) ) {
	define( 'WPM_PLUGIN_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

function wpm() {
	return WP_Multilang::instance();
}

wpm();
