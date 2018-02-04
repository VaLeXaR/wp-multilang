<?php

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set filter for options
 *
 * Class WPM_Options
 * @package WPM/Includes
 * @author   Valentyn Riaboshtan
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
		$config               = wpm_get_config();
		$this->options_config = $config['options'];

		foreach ( $this->options_config as $key => $option ) {
			add_filter( "option_{$key}", 'wpm_translate_value', 5 );
			add_action( "add_option_{$key}", 'update_option', 99, 2 );
			add_filter( "pre_update_option_{$key}", array( $this, 'wpm_update_option' ), 99, 3 );
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

		$this->options_config[ $option ] = apply_filters( "wpm_option_{$option}_config", isset( $this->options_config[ $option ] ) ? $this->options_config[ $option ] : null );

		if ( null === $this->options_config[ $option ] ) {
			return $value;
		}

		remove_filter( "option_{$option}", 'wpm_translate_value', 5 );
		$old_value = get_option( $option );
		add_filter( "option_{$option}", 'wpm_translate_value', 5 );
		$value = wpm_set_new_value( $old_value, $value, $this->options_config[ $option ] );

		return $value;
	}
}
