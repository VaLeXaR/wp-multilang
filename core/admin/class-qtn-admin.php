<?php
/**
 * GamePortal Admin
 *
 * @class    GP_Admin
 * @author   VaLeXaR
 * @category Admin
 * @package  GamePortal/Admin
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
//		include_once( 'gp-admin-functions.php' );
//		new GP_Admin_Menus();
//		GP_Admin_Notices::init();
		new QtN_Admin_Posts();
//		new GP_Admin_Taxonomies();
//		new GP_Admin_Assets();
//		new GP_Admin_API_Keys();
	}
}
