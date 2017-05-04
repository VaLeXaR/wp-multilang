<?php
/**
 * Display notices in admin
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     GamePortal/Admin
 */

namespace GP\Admin;
use GP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GP_Admin_Notices Class.
 */
class GP_Admin_Notices {

	/**
	 * Stores notices.
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback.
	 * @var array
	 */
	private static $core_notices = array(
		'update'              => 'update_notice',
	);

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$notices = get_option( 'game_portal_admin_notices', array() );

		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'portal_notices' ) );

		if ( current_user_can( 'manage_game_portal' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function portal_notices() {
		update_option( 'game_portal_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Show a notice.
	 * @param string $name
	 */
	public static function add_notice( $name ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 * @param  string $name
	 */
	public static function remove_notice( $name ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'game_portal_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 * @param  string  $name
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices() );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['gp-hide-notice'] ) && isset( $_GET['_gp_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_gp_notice_nonce'], 'game_portal_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'game-portal' ) );
			}

			if ( ! current_user_can( 'manage_game_portal' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'game-portal' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['gp-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'game_portal_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			wp_enqueue_style( 'game-portal-activation', gp_asset_path('css/activation.css'), GP_PLUGIN_FILE );
			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'game_portal_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
				}
			}
		}
	}

	/**
	 * Add a custom notice.
	 * @param string $name
	 * @param string $notice_html
	 */
	public static function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'game_portal_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'game_portal_admin_notice_' . $notice );

					if ( $notice_html ) {
						include( 'views/html-notice-custom.php' );
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {
		if ( version_compare( get_option( 'game_portal_db_version' ), GP_VERSION, '<' ) ) {
			$updater = new GP\GP_Background_Updater();
			if ( $updater->is_updating() || ! empty( $_GET['do_update_game_portal'] ) ) {
				include( 'views/html-notice-updating.php' );
			} else {
				include( 'views/html-notice-update.php' );
			}
		} else {
			include( 'views/html-notice-updated.php' );
		}
	}
}
