<?php

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Admin_Options {

	public function __construct() {
		$this->init();

	}

	public function init() {
		$settings = wpm_get_settings();
		$options  = $settings['options'];
		foreach ( $options as $option ) {
			add_filter( "pre_update_option_{$option}", array($this, 'wpm_update_option'), 99, 3 );
		}
	}


	public function wpm_update_option( $value, $old_value, $option ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		remove_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$old_value = get_option( $option );
		add_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$strings   = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $strings, $value );
		$new_value = wpm_ml_value_to_string( $new_value );

		return $new_value;
	}
}
