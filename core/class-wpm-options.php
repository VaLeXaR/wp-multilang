<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Options {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		$settings = wpm_get_settings();

		foreach ( $settings['options'] as $option ) {
			add_filter( "option_{$option}", 'wpm_translate_value', 0 );
		}
	}
}
