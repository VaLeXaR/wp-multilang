<?php

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Admin_Options {

	public function __construct() {
		$this->init();

	}

	public function init() {
		$settings = qtn_get_settings();
		$options  = $settings['options'];
		foreach ( $options as $option ) {
			add_filter( "pre_update_option_{$option}", array($this, 'qtn_update_option'), 0, 3 );
		}
	}


	public function qtn_update_option( $value, $old_value, $option ) {

		if ( qtn_is_ml_value( $value ) ) {
			return $value;
		}

		remove_filter( "option_{$option}", 'qtn_translate_value', 0 );
		$old_value = get_option( $option );
		add_filter( "option_{$option}", 'qtn_translate_value', 0 );
		$strings   = qtn_value_to_ml_array( $old_value );
		$new_value = qtn_set_language_value( $strings, $value );
		$new_value = qtn_ml_value_to_string( $new_value );

		return $new_value;
	}
}
