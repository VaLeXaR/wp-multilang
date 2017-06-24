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
		$this->init();
	}


	/**
	 * Set filters for options in config
	 */
	public function init() {

		$config         = wpm_get_config();
		$options_config = apply_filters( 'wpm_options_config', $config['options'] );

		foreach ( $options_config as $key => $option ) {
			add_filter( "option_{$key}", 'wpm_translate_value', 0 );
		}
	}
}
