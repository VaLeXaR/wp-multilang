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
		add_action( 'admin_init', array( $this, 'init' ), 1 );
		add_action( 'admin_head', array( $this, 'set_edit_lang' ), 0 );
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
	public function init() {
		new QtN_Admin_Menus();
		new QtN_Admin_Posts();
		new QtN_Admin_Taxonomies();
		new QtN_Admin_Settings();
		new QtN_Admin_Edit_Menus();
		new QtN_Admin_Options();
		new QtN_Admin_Widgets();
		new QtN_Admin_Assets();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once( 'qtn-admin-functions.php' );
	}


	public function set_edit_lang() {

		if ( isset( $_GET['edit_lang'] ) || ! isset( $_COOKIE['edit_language'] ) ) {
			$default_locale = qtn_get_default_locale();
			$languages = qtn_get_languages();
			$lang      = qtn_clean( $_GET['edit_lang'] );
			if ( ! in_array( $lang, $languages ) ) {
				$lang = $languages[ $default_locale ];
			}
			qtn_setcookie( 'edit_language', $lang, time() + MONTH_IN_SECONDS );
		}
	}
}
