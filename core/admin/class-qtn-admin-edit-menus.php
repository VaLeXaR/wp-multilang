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
			add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_item' ), 0 );
		}


		public function translate_menu_item( $menu_item ) {
			$menu_item = qtn_translate_object( $menu_item );
			return $menu_item;
		}
	}

endif;
