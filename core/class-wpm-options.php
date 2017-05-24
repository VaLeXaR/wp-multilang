<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set filter for options
 *
 * Class WPM_Options
 * @package WPM\Core
 */
class WPM_Options {

	/**
	 * WPM_Options constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}


	/**
	 * Set filters for options in config
	 */
	public function init() {

		$config = wpm_get_config();

		foreach ( $config['options'] as $key => $option ) {
			add_filter( "option_{$key}", 'wpm_translate_value', 0 );
		}
	}
}
