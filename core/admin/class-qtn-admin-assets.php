<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     qTranslateNext/Admin
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QtN_Admin_Assets' ) ) :

	/**
	 * WC_Admin_Assets Class.
	 */
	class QtN_Admin_Assets {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
//		add_action( 'admin_head',            array( $this, 'level_taxonomy_styles' ) );
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
	//		wp_register_style( 'qtn_language_switcher', qtn_asset_path('css/menu.css'), array(), QTN_VERSION );
	//		wp_register_style( 'game_portal_admin', qtn_asset_path('css/admin.css'), array(), QTN_VERSION );
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
			$settings  = qtn_get_settings();

			// Register scripts
			wp_register_script( 'qtn_language_switcher', qtn_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array(
				'jquery',
				'underscore'
			), QTN_VERSION );

			foreach ( $settings['admin_pages'] as $page_id ) {
				if ( $screen_id == $page_id ) {
					$this->set_language_switcher();
				}
			}

			if ( in_array( $screen->post_type, $settings['post_types'] ) ) {
				$this->set_language_switcher();
			}

			if ( in_array( $screen->taxonomy, $settings['taxonomies'] ) ) {
				$this->set_language_switcher();
			}
		}

		/**
		 * Admin Head.
		 *
		 * Outputs some styles in the admin <head> to show icons on the game-portal admin pages.
		 */
		public function level_taxonomy_styles() {

			if ( ! current_user_can( 'manage_game_portal' ) ) {
				return;
			}
			?>
			<style type="text/css">
				<?php if ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='game' ) : ?>
				.term-slug-wrap, .inline-edit-col label:nth-child(2), .term-description-wrap {
					display: none;
				}

				<?php endif; ?>
			</style>
			<?php
		}


		public function set_language_switcher() {
			$lang = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : qtn_clean( $_COOKIE['edit_language'] );
			wp_enqueue_script( 'qtn_language_switcher' );
			$params = array(
				'switcher' => gp_get_template_html( 'language-switcher.tpl' ),
				'lang'     => $lang
			);
			wp_localize_script( 'qtn_language_switcher', 'qtn_params', $params );
		}
	}

endif;
