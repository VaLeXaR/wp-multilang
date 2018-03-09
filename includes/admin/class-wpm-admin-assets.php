<?php
/**
 * Load assets
 *
 * @author   Valentyn Riaboshtan
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
		wp_register_style( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css', array(), '4.0.5' );
	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts
		wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js', array( 'jquery' ), null );
		wp_register_script( 'wpm_languages', wpm_asset_path( 'scripts/languages' . $suffix . '.js' ), array(
			'wp-util',
			'jquery-ui-sortable',
			'select2',
		), WPM_VERSION );

		wp_register_script( 'wpm_language_switcher', wpm_asset_path( 'scripts/language-switcher' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );
		wp_register_script( 'wpm_language_switcher_customizer', wpm_asset_path( 'scripts/customizer' . $suffix . '.js' ), array( 'wp-util' ), WPM_VERSION );

		wp_register_script( 'wpm_translator', wpm_asset_path( 'scripts/translator' . $suffix . '.js' ), array(), WPM_VERSION );

		$translator_params = array(
			'languages'                 => array_keys( wpm_get_languages() ),
			'default_language'          => wpm_get_default_language(),
			'language'                  => wpm_get_language(),
			'show_untranslated_strings' => get_option( 'wpm_show_untranslated_strings', 'yes' ),
		);
		wp_localize_script( 'wpm_translator', 'wpm_translator_params', $translator_params );

		wp_register_script( 'wpm_additional_settings', wpm_asset_path( 'scripts/additional-settings' . $suffix . '.js' ), array( 'jquery' ), WPM_VERSION );

		if ( null === $screen ) {
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

		if ( ( ( $screen_id === $screen->post_type ) || ( 'edit-' . $screen->post_type === $screen_id ) ) && $screen->post_type && null !== wpm_get_post_config( $screen->post_type ) ) {
			$show_switcher = true;
		}

		if ( ( 'edit-' . $screen->taxonomy === $screen_id ) && $screen->taxonomy && null !== wpm_get_taxonomy_config( $screen->taxonomy ) ) {
			$show_switcher = true;
		}

		$config             = wpm_get_config();
		$admin_pages_config = apply_filters( 'wpm_admin_pages', $config['admin_pages'] );

		if ( in_array( $screen_id, $admin_pages_config, true ) ) {
			$show_switcher = true;
		}

		if ( $show_switcher ) {
			$this->set_language_switcher();

			$admin_html_tags = apply_filters( 'wpm_admin_html_tags', $config['admin_html_tags'] );

			if ( ! empty( $admin_html_tags[ $screen_id ] ) ) {
				wp_enqueue_script( 'wpm_translator' );
				$js_code = '';
				foreach ( ( array ) $admin_html_tags[ $screen_id ] as $attr => $selector ) {
					$js_code .= '$( "' . implode( ', ', $selector ) . '" ).each( function () {';
					if ( 'text' === $attr ) {
						$js_code .= '$(this).text(wpm_translator.translate_string($(this).text()));';
					} elseif ( 'value' === $attr ) {
						$js_code .= '$(this).val(wpm_translator.translate_string($(this).val()));';
					} else {
						$js_code .= '$(this).attr("' . $attr . '", wpm_translator.translate_string($(this).attr("' . $attr . '")));';
					}
					$js_code .= '} );';
				}
				wpm_enqueue_js( $js_code );
			}
		}

		if ( 'options-general' === $screen_id ) {
			wpm_enqueue_js( "$('#WPLANG').parents('tr').hide();" );
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
				#wpbody-content > .wrap {
					padding-top: 37px;
					position: relative;
				}
			</style>
			<?php
		} );

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher' );
	}
}
