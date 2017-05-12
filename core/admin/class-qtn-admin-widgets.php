<?php

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Admin_Widgets {

	public function __construct() {
		add_filter( 'pre_update_option', array( $this, 'save_widgets' ), 0, 2 );
	}

	public function save_widgets( $value, $option ) {

		if ( ! is_bool( strpos( $option, 'widget' ) ) ) {
			foreach ( $value as $key => &$widget ) {
				remove_filter( "option_{$option}", 'qtn_translate_value', 0 );
				$old_value = get_option( $option );
				add_filter( "option_{$option}", 'qtn_translate_value', 0 );
				$strings   = qtn_value_to_ml_array( $old_value[$key]['title'] );
				$new_value = qtn_set_language_value( $strings, $widget['title'] );
				$new_value = qtn_ml_value_to_string( $new_value );
				$widget['title'] = $new_value;
			}
		}

		return $value;
	}
}



//widget_display_callback
//widget_update_callback
//widget_form_callback
//in_widget_form
//widget_title
