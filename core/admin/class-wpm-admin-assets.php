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
	 * WPM_Admin_Assets Class.
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

			// Register admin styles
			wp_register_style( 'wpm_language_switcher', wpm_asset_path( 'styles/main.css' ), array(), WPM_VERSION );
		}


		/**
		 * Enqueue scripts.
		 */
		public function admin_scripts() {

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$config    = wpm_get_config();

			// Register scripts
			wp_register_script( 'wpm_main', wpm_asset_path( 'scripts/main' . $suffix . '.js' ), array(
				'jquery-ui-sortable'
			), WPM_VERSION );
			wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array(
				'jquery',
				'underscore'
			), WPM_VERSION );
			wp_register_script( 'wpm_language_switcher_customizer', wpm_asset_path( 'scripts/customizer' . $suffix . '.js' ), array(
				'jquery',
				'underscore'
			), WPM_VERSION );

			if ( 'customize' == $screen_id ) {
				wp_enqueue_style( 'wpm_language_switcher' );
				wp_enqueue_script( 'wpm_language_switcher_customizer' );
				$params = array(
					'switcher' => gp_get_template_html( 'language-switcher-customizer.tpl' )
				);
				wp_localize_script( 'wpm_language_switcher_customizer', 'wpm_params', $params );
			}

			foreach ( $config['admin_pages'] as $page_id ) {
				if ( $screen_id == $page_id ) {
					$this->set_language_switcher();
				}
			}

			$posts_config = $config['post_types'];
			$posts_config = apply_filters( "wpm_posts_config", $posts_config );

			if ( isset( $posts_config[ $screen->post_type ] ) && ! is_null( $posts_config [ $screen->post_type ] ) ) {
				$this->set_language_switcher();
			}

			$taxonomies_config = $config['taxonomies'];
			$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );

			if ( isset( $taxonomies_config[ $screen->taxonomy ] ) && ! is_null( $taxonomies_config[ $screen->taxonomy ] ) ) {
				$this->set_language_switcher();
			}

			if ( 'options-general' == $screen_id ) {
				wp_enqueue_script( 'wpm_main' );

				$params = array(
					'plugin_url'        => WPM()->plugin_url(),
					'ajax_url'          => admin_url( 'admin-ajax.php' ),
					'delete_lang_nonce' => wp_create_nonce( 'delete-lang' ),
					'confirm_question'  => __( 'Are you sure you want to delete this language?', 'wpm' )
				);

				wp_localize_script( 'wpm_main', 'wpm_main_params', $params );
			}
		}


		public function set_language_switcher() {
			wp_enqueue_style( 'wpm_language_switcher' );
			wp_enqueue_script( 'wpm_language_switcher' );
			$params = array(
				'switcher' => gp_get_template_html( 'language-switcher.tpl' )
			);
			wp_localize_script( 'wpm_language_switcher', 'wpm_params', $params );
		}
	}

endif;
