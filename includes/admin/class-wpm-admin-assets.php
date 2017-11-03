<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     WPM/Includes/Admin
 * @class       WPM_Admin_Assets
 * @version     1.0.4
 */

namespace WPM\Includes\Admin;

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
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register admin styles
		wp_enqueue_style( 'wpm_language_switcher', wpm_asset_path( 'styles/admin/admin' . $suffix . '.css' ), array(), WPM_VERSION );
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
			'wp-util',
			'jquery-ui-sortable',
		), WPM_VERSION );

		$main_params = array(
			'plugin_url'             => wpm()->plugin_url(),
			'flags_dir'             => wpm_get_flags_dir(),
			'ajax_url'               => admin_url( 'admin-ajax.php' ),
			'delete_lang_nonce'      => wp_create_nonce( 'delete-lang' ),
			'confirm_question'       => __( 'Are you sure you want to delete this language?', 'wp-multilang' ),
			'available_translations' => wpm_get_available_translations(),
		);
		wp_localize_script( 'wpm_main', 'wpm_params', $main_params );

		wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );
		wp_register_script( 'wpm_language_switcher_customizer', wpm_asset_path( 'scripts/customizer' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );

		wp_register_script( 'wpm_translator', wpm_asset_path( 'scripts/translator' . $suffix . '.js' ), array(), WPM_VERSION );

		$translator_params = array(
			'languages'                 => array_keys( wpm_get_languages() ),
			'default_language'          => wpm_get_default_language(),
			'language'                  => wpm_get_language(),
			'show_untranslated_strings' => get_option( 'wpm_show_untranslated_strings' ),
		);
		wp_localize_script( 'wpm_translator', 'wpm_translator_params', $translator_params );

		if ( is_null( $screen ) ) {
			return;
		}

		if ( 'customize' === $screen_id ) {

			$languages = wpm_get_languages();
			if ( count( $languages ) > 1 ) {
				wp_enqueue_script( 'wpm_language_switcher_customizer' );
				add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher_customizer' );
			}
		}

		$show_switcher = false;
		$posts_config  = $config['post_types'];

		if ( $screen->post_type && isset( $posts_config [ $screen->post_type ] ) && ! is_null( $posts_config [ $screen->post_type ] ) && ( ( $screen_id == $screen->post_type ) || ( 'edit-' . $screen->post_type == $screen_id ) ) ) {
			$show_switcher = true;
		}

		$taxonomies_config = $config['taxonomies'];

		if ( $screen->taxonomy && isset( $taxonomies_config [ $screen->taxonomy ] ) && ! is_null( $taxonomies_config[ $screen->taxonomy ] ) && ( 'edit-' . $screen->taxonomy == $screen_id ) ) {
			$show_switcher = true;
		}

		$admin_pages_config = apply_filters( 'wpm_admin_pages', $config['admin_pages'] );

		if ( in_array( $screen_id, $admin_pages_config, true ) ) {
			$show_switcher = true;
		}

		if ( $show_switcher ) {
			$this->set_language_switcher();
		}

		if ( 'options-general' === $screen_id ) {
			wp_enqueue_script( 'wpm_main' );
		}

		$admin_html_tags = apply_filters( 'wpm_admin_html_tags', $config['admin_html_tags'] );

		foreach ( $admin_html_tags as $html_screen => $html_config ) {
			if ( $html_screen === $screen_id ) {
				wp_enqueue_script( 'wpm_translator' );
				$js_code = '(function ( $ ) {';
				foreach ( $html_config as $attr => $selector ) {
					$js_code .= '$( "' . implode( ', ', $selector ) . '" ).each( function () {';
					if ( 'text' == $attr ) {
						$js_code .= '$(this).text(wpm_translator.translate_string($(this).text()));';
					} elseif ( 'value' == $attr ) {
						$js_code .= '$(this).val(wpm_translator.translate_string($(this).val()));';
					} else {
						$js_code .= '$(this).attr("' . $attr . '", wpm_translator.translate_string($(this).attr("' . $attr . '")));';
					}
					$js_code .= '} );';
				}
				$js_code .= '})( window.jQuery );';
				wpm_enqueue_js( $js_code );
			}
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

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher' );
	}
}
