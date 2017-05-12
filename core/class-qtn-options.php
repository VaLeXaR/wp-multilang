<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Options {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		$settings = qtn_get_settings();

		foreach ( $settings['options'] as $option ) {
			add_filter( "option_{$option}", 'qtn_translate_value', 0 );
		}
	}
}
