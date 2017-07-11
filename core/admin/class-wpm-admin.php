<?php
/**
 * WPM Admin
 *
 * @class      WPM_Admin
 * @author     VaLeXaR
 * @category   Admin
 * @package    WPM/Core/Admin
 * @version    1.0.2
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Admin class.
 */
class WPM_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_init', array( $this, 'init' ), 1 );
		add_action( 'admin_head', array( $this, 'set_edit_lang' ), 0 );
		add_action( 'admin_footer', 'wpm_print_js', 25 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 * @since 1.1.2
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/wpm-admin-functions.php' );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function init() {
		new WPM_Admin_Menus();
		new WPM_Admin_Edit_Menus();
		new WPM_Admin_Posts();
		new WPM_Admin_Taxonomies();
		new WPM_Admin_Settings();
		new WPM_Admin_Widgets();
		new WPM_Admin_Assets();
		new WPM_Admin_Customizer();
	}

	/**
	 * Add cookie for 'edit_lang'
	 */
	public function set_edit_lang() {

		if ( isset( $_GET['edit_lang'] ) || ! isset( $_COOKIE['edit_language'] ) ) {
			wpm_setcookie( 'edit_language', wpm_get_language(), time() + MONTH_IN_SECONDS );
		}
	}
}
