<?php

namespace WPM\Includes;

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
		'1.8.0' => array(
			'wpm_update_180_flags',
			'wpm_update_180_db_version',
		),
		'1.8.2' => array(
			'wpm_update_182_options',
			'wpm_update_182_db_version',
		),
	);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
	}

	/**
	 * Check WPM version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( get_option( 'wpm_version' ) !== wpm()->version ) {
			self::install();
			do_action( 'wpm_updated' );
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

		self::create_options();
		WPM_Config::load_config_run();
		self::update_wpm_version();
		self::maybe_update_db_version();

		delete_transient( 'wpm_installing' );

		// Trigger action
		do_action( 'wpm_installed' );
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'wpm_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ( ! is_null( $current_db_version ) ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * See if we need to show or run database updates during install.
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			self::update();
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update WPM version to current.
	 */
	private static function update_wpm_version() {

		/* fix for update */
		if ( $old_version = get_option( 'wpm_version' ) ) {
			if ( version_compare( $old_version, '1.7.7', 'le' ) ) {
				add_option( 'wpm_db_version', '1.7.7' );
			}
		}

		delete_option( 'wpm_version' );
		add_option( 'wpm_version', wpm()->version );
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

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					include_once( dirname( __FILE__ ) . '/wpm-update-functions.php' );
					if ( is_callable( $update_callback ) ) {
						call_user_func( $update_callback );
					}
				}
			}
		}
	}

	/**
	 * Update DB version to current.
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'wpm_db_version' );
		add_option( 'wpm_db_version', is_null( $version ) ? wpm()->version : $version );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		$languages              = array();
		$available_translations = wpm_get_available_translations();
		$default_locale         = wpm_get_default_locale();
		$default_language       = '';

		foreach ( wpm_get_installed_languages() as $locale ) {
			$slug = sanitize_title( current( $available_translations[ $locale ]['iso'] ) );
			if ( $locale == $default_locale ) {
				$default_language = $slug;
			}
			$languages[ $slug ] = array(
				'enable'      => 1,
				'locale'      => $locale,
				'name'        => $available_translations[ $locale ]['native_name'],
				'translation' => $locale,
				'date'        => '',
				'time'        => '',
				'flag'        => $slug . '.png',
			);
		}

		add_option( 'wpm_languages', $languages );
		add_option( 'wpm_site_language', $default_language );
	}
}
