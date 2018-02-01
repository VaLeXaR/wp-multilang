<?php

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set filter for options
 *
 * Class WPM_Site_Options
 * @package WPM/Includes
 * @author   Valentyn Riaboshtan
 */
class WPM_Site_Options {

	public $site_options_config = array();

	/**
	 * WPM_Options constructor.
	 */
	public function __construct() {
		add_filter( 'get_network', array( $this, 'translate_network_name' ) );

		$config                    = wpm_get_config();
		$this->site_options_config = $config['site_options'];

		foreach ( $this->site_options_config as $key => $option ) {
			add_filter( "site_option_{$key}", 'wpm_translate_value', 5 );
			add_filter( "pre_update_site_option_{$key}", array( $this, 'wpm_update_site_option' ), 99, 3 );
			add_filter( "pre_add_site_option_{$key}", array( $this, 'wpm_add_site_option' ), 99, 2 );
		}
	}


	/**
	 * Translate network name
	 *
	 * @param $_network
	 *
	 * @return mixed
	 */
	public function translate_network_name( $_network ) {
		$_network->site_name = wpm_translate_string( $_network->site_name );

		return $_network;
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

		$this->site_options_config[ $option ] = apply_filters( "wpm_site_option_{$option}_config", isset( $this->site_options_config[ $option ] ) ? $this->site_options_config[ $option ] : null );

		if ( null === $this->site_options_config[ $option ] ) {
			return $value;
		}

		remove_filter( "site_option_{$option}", 'wpm_translate_value', 5 );
		$old_value = get_site_option( $option );
		add_filter( "site_option_{$option}", 'wpm_translate_value', 5 );
		$value = wpm_set_new_value( $old_value, $value, $this->site_options_config[ $option ] );

		return $value;
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

		$this->site_options_config[ $option ] = apply_filters( "wpm_site_option_{$option}_config", isset( $this->site_options_config[ $option ] ) ? $this->site_options_config[ $option ] : null );

		if ( null === $this->site_options_config[ $option ] ) {
			return $value;
		}

		$value = wpm_set_new_value( array(), $value, $this->site_options_config[ $option ] );

		return $value;
	}
}
