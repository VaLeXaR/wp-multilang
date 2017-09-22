<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set filter for options
 *
 * Class WPM_Site_Options
 * @package WPM\Core
 * @since 1.7.0
 */
class WPM_Site_Options {

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

		$config = wpm_get_config();

		foreach ( $config['site_options'] as $key => $option ) {
			add_filter( "site_option_{$key}", 'wpm_translate_value', 0 );
			add_filter( "pre_update_site_option_{$key}", array( $this, 'wpm_update_site_option' ), 99, 3 );
			add_filter( "pre_add_site_option_{$key}",  array( $this, 'wpm_add_site_option' ), 99, 2 );
		}
	}


	/**
	 * Update site options with translate
	 *
	 * @param $value
	 * @param $old_value
	 * @param $option
	 *
	 * @return array|bool|mixed|string
	 */
	public function wpm_update_site_option( $value, $old_value, $option ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		$config                    = wpm_get_config();
		$options_config            = $config['site_options'];
		$options_config[ $option ] = apply_filters( "wpm_site_option_{$option}_config", isset( $options_config[ $option ] ) ? $options_config[ $option ] : null );

		if ( is_null( $options_config[ $option ] ) ) {
			return $value;
		}

		remove_filter( "site_option_{$option}", 'wpm_translate_value', 0 );
		$old_value = get_site_option( $option );
		add_filter( "site_option_{$option}", 'wpm_translate_value', 0 );
		$strings   = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $strings, $value, $options_config[ $option ] );
		$new_value = wpm_ml_value_to_string( $new_value );

		return $new_value;
	}


	/**
	 * Add site options with translate
	 *
	 * @param mixed $value
	 * @param string $option
	 *
	 * @return array|bool|mixed|string
	 */
	public function wpm_add_site_option( $value, $option ) {

		if ( wpm_is_ml_value( $value ) ) {
			return $value;
		}

		$config                    = wpm_get_config();
		$options_config            = $config['site_options'];
		$options_config[ $option ] = apply_filters( "wpm_site_option_{$option}_config", isset( $options_config[ $option ] ) ? $options_config[ $option ] : null );

		if ( is_null( $options_config[ $option ] ) ) {
			return $value;
		}

		$new_value = wpm_set_language_value( array(), $value, $options_config[ $option ] );
		$new_value = wpm_ml_value_to_string( $new_value );

		return $new_value;
	}
}
