<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Widgets {

	public function __construct() {
		add_filter( 'widget_display_callback', 'wpm_translate_value', 0 );
	}

	public function widget_display( $instance ) {
		return wpm_translate_value( $instance );
	}
}



//widget_display_callback
//widget_update_callback
//widget_form_callback
//in_widget_form
//widget_title
