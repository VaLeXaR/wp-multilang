<?php
/**
 * WPM Admin Functions
 *
 * @author   Valentyn Riaboshtan
 * @category Core
 * @package  WPM/Admin/Functions
 * @since    1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'wp_prepare_attachment_for_js', 'wpm_translate_value' );
