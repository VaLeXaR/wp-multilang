<?php

namespace WPM\Includes;
use WPM\Includes\Admin\WPM_Admin_Notices;
use WPM\Includes\Admin\WPM_Admin_Settings;

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
		'2.0.0' => array(
			'wpm_update_200_options',
			'wpm_update_200_db_version',
		),
		'2.1.0' => array(
			'wpm_update_210_delete_config',
			'wpm_update_210_db_version',
		),
		'2.1.1' => array(
			'wpm_update_211_change_options',
			'wpm_update_211_db_version',
		),
		'2.1.4' => array(
			'wpm_update_214_change_syntax',
			'wpm_update_214_db_version',
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
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . WPM_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
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
		if ( get_option( 'wpm_version' ) !== wpm()->version ) {
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
		if ( ! empty( $_GET['force_update_wpm'] ) ) {
			do_action( 'wp_wpm_updater_cron' );
			wp_safe_redirect( admin_url( 'options-general.php?page=wpm-settings' ) );
			exit;
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
		self::create_roles();
		WPM_Config::load_config_run();
		self::update_wpm_version();
		self::maybe_update_db_version();

		delete_transient( 'wpm_installing' );

		// Trigger action
		do_action( 'wpm_flush_rewrite_rules' );
		do_action( 'wpm_installed' );
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 2.0.0
	 */
	private static function remove_admin_notices() {
		WPM_Admin_Notices::init();
		WPM_Admin_Notices::remove_all_notices();
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since 1.7.8
	 *
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'wpm_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ( null !== $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 1.7.8
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			if ( apply_filters( 'wpm_enable_auto_update_db', false ) ) {
				self::init_background_updater();
				self::update();
			} else {
				WPM_Admin_Notices::add_notice( 'update' );
			}
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
	 * @since 1.7.8
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 *
	 * @since 1.7.8
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
	 *
	 * @since 1.7.8
	 *
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'wpm_db_version' );
		add_option( 'wpm_db_version', null === $version ? wpm()->version : $version );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		$settings = WPM_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'], $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}

		$languages              = array();
		$available_translations = wpm_get_available_translations();

		foreach ( wpm_get_installed_languages() as $locale ) {

			$code = sanitize_title( current( $available_translations[ $locale ]['iso'] ) );
			$flag = explode( '_', strtolower( $locale ) );

			$languages[ $code ] = array(
				'enable'      => 1,
				'locale'      => $locale,
				'name'        => $available_translations[ $locale ]['native_name'],
				'translation' => $locale,
				'date'        => '',
				'time'        => '',
				'flag'        => ( isset( $flag[1] ) ? $flag[1] : $flag[0] ) . '.png',
			);
		}

		add_option( 'wpm_languages', $languages );
	}


	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$capabilities = array(
			'level_7'              => true,
			'level_6'              => true,
			'level_5'              => true,
			'level_4'              => true,
			'level_3'              => true,
			'level_2'              => true,
			'level_1'              => true,
			'level_0'              => true,
			'read'                 => true,
			'read_private_pages'   => true,
			'read_private_posts'   => true,
			'edit_posts'           => true,
			'edit_pages'           => true,
			'edit_published_posts' => true,
			'edit_published_pages' => true,
			'edit_private_pages'   => true,
			'edit_private_posts'   => true,
			'edit_others_posts'    => true,
			'edit_others_pages'    => true,
			'manage_categories'    => true,
			'manage_links'         => true,
			'upload_files'         => true,
		);

		// Translator role
		$result = add_role( 'translator', __( 'Translator', 'wp-multilang' ), $capabilities );

		if ( null === $result ) {
			$translator = get_role( 'translator' );

			foreach ( $capabilities as $cap => $value ) {
				$translator->add_cap( $cap );
			}
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'translator', $cap );
			}
		}

		$wp_roles->add_cap( 'administrator', 'manage_translations' );
	}

	/**
	 * Get capabilities for WP Multilang - these are assigned to translator during installation or reset.
	 *
	 * @return array
	 */
	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_translations',
		);

		$capability_types = apply_filters( 'wpm_role_translator_capability_types', array() );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"read_private_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
			);
		}

		return apply_filters( 'wpm_role_translator_capabilities', $capabilities );
	}


	/**
	 * wpm_remove_roles function.
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'translator', $cap );
			}
		}

		$wp_roles->remove_cap( 'administrator', 'manage_translations' );

		remove_role( 'translator' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links
	 *
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=wpm-settings' ) . '" aria-label="' . esc_attr__( 'View WP Multilang settings', 'wp-multilang' ) . '">' . esc_html__( 'Settings', 'wp-multilang' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}
}
