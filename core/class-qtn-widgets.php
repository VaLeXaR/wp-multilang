<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Widgets {

	public function __construct() {

		add_filter( 'widget_title', 'qtn_translate_string', 0 );
	}

	/*public function get_widget_title( $title, $instance, $id_base ) {
		s( $title, $instance, $id_base );
		die();

		return $title;
	}*/
}



//widget_display_callback
//widget_update_callback
//widget_form_callback
//in_widget_form
//widget_title
