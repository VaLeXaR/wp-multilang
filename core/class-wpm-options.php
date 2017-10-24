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
 * @version 1.2.0
 */
class WPM_Options {

	/**
	 * Options config
	 *
	 * @var array
	 */
	public $options_config = array();

	/**
	 * WPM_Options constructor.
	 */
	public function __construct() {
		$config = wpm_get_config();
		$this->options_config = $config['options'];
		$this->init();
	}


	/**
	 * Set filters for options in config
	 */
	public function init() {

		foreach ( $this->options_config as $key => $option ) {
			add_filter( "option_{$key}", 'wpm_translate_value', 0 );
			add_action( "add_option_{$key}", 'update_option', 99, 2 );
			add_filter( "pre_update_option_{$key}", array( $this, 'wpm_update_option' ), 99, 3 );
		}

		add_filter( 'option_date_format', array( $this, 'set_date_format' ) );
		add_filter( 'option_time_format', array( $this, 'set_time_format' ) );
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

		$this->options_config[ $option ] = apply_filters( "wpm_option_{$option}_config", isset( $this->options_config[ $option ] ) ? $this->options_config[ $option ] : null );

		if ( is_null( $this->options_config[ $option ] ) ) {
			return $value;
		}

		remove_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$old_value = get_option( $option );
		add_filter( "option_{$option}", 'wpm_translate_value', 0 );
		$strings   = wpm_value_to_ml_array( $old_value );
		$new_value = wpm_set_language_value( $strings, $value, $this->options_config[ $option ] );
		$new_value = wpm_ml_value_to_string( $new_value );

		return $new_value;
	}


	/**
	 * Set date format for current language
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function set_date_format( $value ) {
		$options = wpm_get_options();
		$locale  = get_locale();

		if ( ( ! is_admin() || wp_doing_ajax() ) && $options[ $locale ]['date'] ) {
			return $options[ $locale ]['date'];
		}

		return $value;
	}


	/**
	 * Set time format for current language
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function set_time_format( $value ) {
		$options = wpm_get_options();
		$locale  = get_locale();

		if ( ( ! is_admin() || wp_doing_ajax() ) && $options[ $locale ]['time'] ) {
			return $options[ $locale ]['time'];
		}

		return $value;
	}
}
