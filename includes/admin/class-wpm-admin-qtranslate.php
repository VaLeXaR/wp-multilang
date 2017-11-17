<?php
/**
 * Handles migration of qTranslate / qTranslate-X stuff.
 *
 * @author   Soft79
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Qtranslate {

	/**
	 * WPM_Admin_Qtx constructor.
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'handle_qtranslate' ) );
	}

	/**
	 * Handle qTranslate admin stuff
	 */
	public function handle_qtranslate() {
		//qTranslate must be disabled
		if ( $qtranslate = $this->detect_qtranslate() ) {
			WPM_Admin_Notices::add_custom_notice( 'qtranslate_active', sprintf( __( '%s is active. Please deactivate it.', 'wp-multilang' ), $qtranslate ), 'error' );
		}
	}


	/**
	 * Detects whether qTranslate or qTranslate-X is active.
	 * Returns the name of the plugin if it's detected, false otherwise.
	 *
	 * @return bool|string Either false or the plugin name
	 */
	private function detect_qtranslate() {
		if ( defined( 'QTX_VERSION' ) ) {
			return 'qTranslate-X';
		}
		if ( defined( 'QT_SUPPORTED_WP_VERSION' ) ) {
			return 'qTranslate';
		}

		return false;
	}

}
