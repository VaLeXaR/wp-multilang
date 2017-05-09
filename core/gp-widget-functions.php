<?php
/**
 * qTranslateNext Widget Functions
 *
 * Widget related functions and widget registration.
 *
 * @author 		VaLeXaR
 * @category 	Core
 * @package 	qTranslateNext/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include widget classes.
include_once( 'abstracts/abstract-gp-widget.php' );

/**
 * Register Widgets.
 */
function gp_register_widgets() {
	register_widget( 'GP\Widgets\GP_Widget_Popular_Games' );
}
add_action( 'widgets_init', 'gp_register_widgets' );
