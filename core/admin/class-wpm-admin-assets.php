<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     WPM/Core/Admin
 * @class       WPM_Admin_Assets
 * @version     1.0.3
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		wp_enqueue_style( 'wpm_language_switcher', wpm_asset_path( 'styles/admin/admin.css' ), array(), WPM_VERSION );
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

		$main_params = array(
			'plugin_url'             => WPM()->plugin_url(),
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'delete_lang_nonce'      => wp_create_nonce( 'delete-lang' ),
			'confirm_question'       => __( 'Are you sure you want to delete this language?', 'wpm' ),
			'available_translations' => wpm_get_available_translations(),
		);
		wp_localize_script( 'wpm_main', 'wpm_params', $main_params );

		wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );
		wp_register_script( 'wpm_language_switcher_customizer', wpm_asset_path( 'scripts/customizer' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );

		wp_register_script( 'wpm_translator', wpm_asset_path( 'scripts/translator' . $suffix . '.js' ), array(), WPM_VERSION );

		$translator_params = array(
			'languages'                 => array_values( wpm_get_languages() ),
			'default_language'          => wpm_get_languages()[ wpm_get_default_locale() ],
			'language'                  => wpm_get_language(),
			'show_untranslated_strings' => get_option( 'wpm_show_untranslated_strings' ),
		);
		wp_localize_script( 'wpm_translator', 'wpm_translator_params', $translator_params );

		if ( 'customize' === $screen_id ) {

			$languages = wpm_get_languages();
			if ( count( $languages ) <= 1 ) {
				return;
			}

			wp_enqueue_script( 'wpm_language_switcher_customizer' );
			add_action( 'admin_print_footer_scripts', function () {
				echo wpm_get_template_html( 'language-switcher-customizer.php' );
			} );
		}

		$show_switcher = false;

		$admin_pages_config = apply_filters( 'wpm_admin_pages', $config['admin_pages'] );

		if ( in_array( $screen_id, $admin_pages_config, true ) ) {
			$show_switcher = true;
		}

		if ( ! is_null( $screen ) ) {

			$posts_config = $config['post_types'];

			if ( $screen->post_type && ! is_null( $posts_config [ $screen->post_type ] ) && ! $screen->taxonomy ) {
				$show_switcher = true;
			}

			$taxonomies_config = $config['taxonomies'];

			if ( $screen->taxonomy && ! is_null( $taxonomies_config[ $screen->taxonomy ] ) ) {
				$show_switcher = true;
			}
		}

		if ( $show_switcher ) {
			$this->set_language_switcher();
		}

		if ( 'options-general' === $screen_id ) {
			wp_enqueue_script( 'wpm_main' );
		}
	}

	/**
	 * Display language switcher for edit posts, taxonomies, options
	 */
	public function set_language_switcher() {

		$languages = wpm_get_languages();
		if ( count( $languages ) <= 1 ) {
			return;
		}

		wp_enqueue_script( 'wpm_language_switcher' );

		add_action( 'admin_head', function () {
			?>
			<style>
				#wpbody-content .wrap {
					padding-top: 37px;
					position: relative;
				}
			</style>
			<?php
		} );

		add_action( 'admin_print_footer_scripts', function () {
			echo wpm_get_template_html( 'language-switcher.php' );
		} );
	}
}
