<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Options {

	public function __construct() {

		add_filter( 'after_setup_theme', array( $this, 'get_options' ), 1 );
	}

	public function get_options() {
		global $qtn_config;

		$options = $qtn_config->settings['options'];

		foreach ( $options as $option ) {
			add_filter( "option_{$option}", 'qtn_translate_value', 0 );
		}
	}
}
