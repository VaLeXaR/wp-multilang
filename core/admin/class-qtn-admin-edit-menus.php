<?php
/**
 * Setup menus in WP admin.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 * @version  1.0.0
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QtN_Admin_Edit_Menus' ) ) :

	/**
	 * QtN_Admin_Edit_Menus Class.
	 */
	class QtN_Admin_Edit_Menus {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_head', array( $this, 'redirect_to_edit_lang' ), 0 );
		}


		public function redirect_to_edit_lang() {
			global $qtn_config;
			$screen = get_current_screen();

			if ( $screen->id == 'nav-menus' && ! isset( $_GET['action'] ) ) {
				if ( ! isset( $_GET['edit_lang'] ) ) {
					wp_redirect( add_query_arg( 'edit_lang', $qtn_config->languages[ get_locale() ], $_SERVER['REQUEST_URI'] ) );
					exit;
				}
			}
		}
	}

endif;
