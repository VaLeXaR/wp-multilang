<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     WPMPlugin/Admin
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPM_Admin_Assets' ) ) :

	/**
	 * WC_Admin_Assets Class.
	 */
	class WPM_Admin_Assets {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}

		/**
		 * Enqueue styles.
		 */
		public function admin_styles() {
			global $wp_scripts;

			$screen         = get_current_screen();
			$screen_id      = $screen ? $screen->id : '';
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';

			// Register admin styles
			//		wp_register_style( 'wpm_language_switcher', wpm_asset_path('css/menu.css'), array(), WPM_VERSION );
			//		wp_register_style( 'game_portal_admin', wpm_asset_path('css/admin.css'), array(), WPM_VERSION );
			//		wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );

			// Sitewide menu CSS
			//		wp_enqueue_style( 'game_portal_admin_menu' );

			// Admin styles for GP pages only
			//		if ( in_array( $screen_id, gp_get_screen_ids() ) ) {
			//			wp_enqueue_style( 'game_portal_admin' );
			//			wp_enqueue_style( 'jquery-ui-style' );
			//			wp_enqueue_style( 'wp-color-picker' );
			//		}
		}


		/**
		 * Enqueue scripts.
		 */
		public function admin_scripts() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$config  = wpm_get_config();

			// Register scripts
			wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array(
				'jquery',
				'underscore'
			), WPM_VERSION );

			foreach ( $config['admin_pages'] as $page_id ) {
				if ( $screen_id == $page_id ) {
					$this->set_language_switcher();
				}
			}

			if ( isset( $config['post_types'][ $screen->post_type ] ) ) {
				$this->set_language_switcher();
			}

			if ( isset( $config['taxonomies'][ $screen->taxonomy ] ) ) {
				$this->set_language_switcher();
			}
		}


		public function set_language_switcher() {
			wp_enqueue_script( 'wpm_language_switcher' );
			$params = array(
				'switcher' => gp_get_template_html( 'language-switcher.tpl' )
			);
			wp_localize_script( 'wpm_language_switcher', 'wpm_params', $params );
		}
	}

endif;
