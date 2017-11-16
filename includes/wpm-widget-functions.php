<?php
/**
 * WPM Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @category      Core
 * @package       WPM/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Widgets.
 */
function wpm_register_widgets() {
	register_widget( 'WPM\Includes\Widgets\WPM_Widget_Language_Switcher' );
}

add_action( 'widgets_init', 'wpm_register_widgets' );
