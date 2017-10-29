<?php

namespace WPM\Core;
use WPM\Core\Admin\WPM_Admin_Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installation related functions and actions.
 *
 * @category Admin
 * @package  WPMPlugin/Classes
 */
class WPM_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.7.8' => array(
			'wpm_update_178_datetime_format',
			'wpm_update_178_db_version',
		),
	);

	/**
	 * Background update class.
	 *
	 * @var object
	 */
	private static $background_updater;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'after_setup_theme', array( __CLASS__, 'check_version' ), 0 );
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		self::$background_updater = new WPM_Background_Updater();
	}

	/**
	 * Check WPM version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( get_option( 'wpm_version' ) !== WPM()->version ) {
			self::install();
			do_action( 'wpm_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_wpm'] ) ) {
			self::update();
			WPM_Admin_Notices::add_notice( 'update' );
		}
	}

	/**
	 * Install WPM.
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wpm_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wpm_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		wpm_maybe_define_constant( 'WPM_INSTALLING', true );

		self::remove_admin_notices();
		self::create_options();
		WPM_Config::load_config_run();
		self::update_wpm_version();
		self::maybe_update_db_version();

		delete_transient( 'wpm_installing' );

		// Trigger action
		do_action( 'wpm_installed' );
	}

	/**
	 * Reset any notices added to admin.
	 */
	private static function remove_admin_notices() {
		WPM_Admin_Notices::remove_all_notices();
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'wpm_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * See if we need to show or run database updates during install.
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::init_background_updater();
			self::update();
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update WPM version to current.
	 */
	private static function update_wpm_version() {
		delete_option( 'wpm_version' );
		add_option( 'wpm_version', WPM()->version );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'wpm_db_version' );
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Update DB version to current.
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'wpm_db_version' );
		add_option( 'wpm_db_version', is_null( $version ) ? WPM()->version : $version );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		if ( get_option( 'wpm_languages' ) ) {
			return;
		}

		$languages              = array();
		$available_translations = wpm_get_available_translations();

		foreach ( wpm_get_installed_languages() as $language ) {
			$languages[ $language ] = array(
				'name'   => $available_translations[ $language ]['native_name'],
				'date'   => '',
				'time'   => '',
				'slug'   => current( $available_translations[ $language ]['iso'] ),
				'flag'   => current( $available_translations[ $language ]['iso'] ),
				'enable' => 1,
			);
		}

		add_option( 'wpm_languages', $languages );
	}
}
