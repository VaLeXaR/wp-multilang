<?php
/**
 * Save options with translate
 *
 * @class    WPM_Admin_Options
 * @package  WPM\Core\Admin
 * @author   VaLeXaR
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Admin_Options
 */
class WPM_Admin_Options {

	/**
	 * WPM_Admin_Options constructor.
	 */
	public function __construct() {
		$this->init();

	}

	/**
	 * Add filters for options in config
	 */
	public function init() {
		$config         = wpm_get_config();
		$options_config = apply_filters( 'wpm_options_config', $config['options'] );
		foreach ( $options_config as $option => $option_config ) {
			add_filter( "pre_update_option_{$option}", array( $this, 'wpm_update_option' ), 99, 3 );
		}
	}


	/**
	 * Update options with translate
	 *
	 * @param $value
	 * @param $old_value
	 * @param $option
	 *
	 * @return array|bool|mixed|string
	 */
	public function wpm_update_option( $value, $old_value, $option ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		$config         = wpm_get_config();
		$options_config = apply_filters( 'wpm_options_config', $config['options'] );
		$option_config  = apply_filters( "wpm_option_{$option}_config", $options_config[ $option ], $value );
		remove_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$old_value = get_option( $option );
		add_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$strings   = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $strings, $value, $option_config );
		$new_value = wpm_ml_value_to_string( $new_value );

		return $new_value;
	}
}
