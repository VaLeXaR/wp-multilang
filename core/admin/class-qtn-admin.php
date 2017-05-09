<?php
/**
 * qTranslateNext Admin
 *
 * @class    GP_Admin
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 * @version  1.0.0
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * QtN_Admin class.
 */
class QtN_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_footer', 'qtn_print_js', 25 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( 'qtn-admin-functions.php' );
		new QtN_Admin_Menus();
		new QtN_Admin_Posts();
		new QtN_Admin_Settings();
//		new GP_Admin_Taxonomies();
//		new GP_Admin_Assets();
	}
}
