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

		$config = wpm_get_config();

		foreach ( $config['options'] as $key => $option ) {
			add_filter( "option_{$key}", 'wpm_translate_value', 0 );
		}
	}
}
