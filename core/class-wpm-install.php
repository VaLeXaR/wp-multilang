<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installation related functions and actions.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPMPlugin/Classes
 */
class WPM_Install {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'after_setup_theme', array( __CLASS__, 'check_version' ), 0 );
	}

	/**
	 * Check WPM version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( get_option( 'wpm_version' ) !== WPM()->version ) {
			self::install();
		}
	}

	/**
	 * Install WPM.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'WPM_INSTALLING' ) ) {
			define( 'WPM_INSTALLING', true );
		}

		self::create_options();
		WPM_Config::load_config_run();
		self::update_wpm_version();

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

		// Trigger action
		do_action( 'wpm_installed' );
	}

	/**
	 * Update WPM version to current.
	 */
	private static function update_wpm_version() {
		delete_option( 'wpm_version' );
		add_option( 'wpm_version', WPM()->version );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		$languages              = array();
		$available_translations = wpm_get_available_translations();

		foreach ( wpm_get_installed_languages() as $language ) {
			$languages[ $language ] = array(
				'name'   => $available_translations[ $language ]['native_name'],
				'slug'   => current( $available_translations[ $language ]['iso'] ),
				'flag'   => current( $available_translations[ $language ]['iso'] ),
				'enable' => 1,
			);
		}

		add_option( 'wpm_languages', $languages );
	}
}
