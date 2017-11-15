<?php
/**
 * WP Multilang API
 *
 * @category API
 * @package  WPM/API
 */

namespace WPM\Includes;
use WPM\Includes\Admin\WPM_Admin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_API {

	/**
	 * Setup class.
	 * @since 2.0
	 */
	public function __construct() {

		// WP REST API.
		$this->rest_api_init();
	}

	/**
	 * Init WP REST API.
	 */
	private function rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_wp_admin_settings' ), 10 );
	}

	/**
	 * Register WPM settings from WP-API to the REST API.
	 */
	public function register_wp_admin_settings() {
		$pages = WPM_Admin_Settings::get_settings_pages();
		foreach ( $pages as $page ) {
			new WPM_Register_WP_Admin_Settings( $page, 'page' );
		}
	}

}
