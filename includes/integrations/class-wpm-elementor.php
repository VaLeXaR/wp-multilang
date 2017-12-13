<?php
/**
 * Class for capability with Elementor Page Builder
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Elementor
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Elementor {

	/**
	 * WPM_Elementor constructor.
	 */
	public function __construct() {
		add_filter( 'wpm__elementor_data_meta_config', array( $this, 'add_widgets_config' ), 10, 2 );
		add_filter( 'wpm_filter_old__elementor_data_meta_value', array( $this, 'set_meta_value' ), 10, 3 );
		add_filter( 'wpm_get__elementor_data_meta_value', array( $this, 'translate_value' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'add_language_switcher' ) );
	}

	/**
	 * Json encode and set new values
	 *
	 * @param $old_value
	 * @param $meta_value
	 * @param $meta_config
	 *
	 * @return false|string
	 */
	public function set_meta_value( $old_value, $meta_value, $meta_config ) {
		$old_value  = json_decode( $old_value, true );
		$meta_value = json_decode( $meta_value, true );
		$old_value  = $this->filter_old_value( $old_value, $meta_value );
		$meta_value = wpm_set_new_value( $old_value, $meta_value, $meta_config );

		return wp_json_encode( $meta_value );
	}

	/**
	 * Filter old values recursively
	 *
	 * @param $old_value
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	private function filter_old_value( $old_value, $meta_value ) {
		$new_old_value = $meta_value;

		foreach ( $meta_value as $key => $widget ) {
			foreach ( $old_value as $_widget ) {
				if ( ( $widget['id'] === $_widget['id'] ) ) {
					if ( 'widget' !== $widget['elType'] ) {
						$new_old_value[ $key ]['elements'] = $this->filter_old_value( $_widget, $widget );
					} else {
						$new_old_value[ $key ] = $_widget;
					}
				}
			}
		}

		return $new_old_value;
	}

	/**
	 * Add config for widget
	 *
	 * @param $config
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function add_widgets_config( $config, $meta_value ) {
		$meta_value     = json_decode( $meta_value, true );
		$widgets_config = $config;

		if ( is_array( $meta_value ) ) {
			$config = $this->add_recursive_config( array(), $meta_value, $widgets_config );
		}

		return $config;
	}

	/**
	 * Add config for values recursively
	 *
	 * @param $config
	 * @param $value
	 * @param $widgets_config
	 *
	 * @return mixed
	 */
	private function add_recursive_config( $config, $value, $widgets_config ) {
		foreach ( $value as $key => $widget ) {
			if ( 'widget' !== $widget['elType'] ) {
				$config[ $key ]['elements'] = $this->add_recursive_config( $config, $widget['elements'], $widgets_config );
			} else {
				$config[ $key ] = $widgets_config;
			}
		}

		return $config;
	}

	/**
	 * Translate json data
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
	public function translate_value( $value ) {
		$value = json_decode( $value, true );
		$value = wpm_translate_value( $value );

		return wp_json_encode( $value );
	}

	/**
	 * Add language switcher on editor screen
	 */
	public function add_language_switcher() {
		$screen = get_current_screen();

		if ( ! $screen->post_type || is_null( wpm_get_post_config( $screen->post_type ) ) || ( count( wpm_get_languages() ) <= 1 ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'wpm_language_switcher', wpm_asset_path( 'styles/admin/admin' . $suffix . '.css' ), array(), WPM_VERSION );
		add_action( 'admin_print_footer_scripts', array( $this, 'set_ls' ) );
	}

	/**
	 * Display language switcher
	 */
	public function set_ls() {
		wpm_admin_language_switcher_customizer();
		?>
		<script>
			(function( $ ) {
				$(function() {
					$(window).on('load', function(){
						if ($('#wpm-language-switcher').length === 0) {
							var language_switcher = wp.template( 'wpm-ls-customizer' );
							$('#elementor-panel-header-menu-button').after(language_switcher);
						}
					});
				});
			})( jQuery, wp );
		</script>
		<style>
			.wpm-language-switcher {
				position: relative;
				left: auto;
				width: 40px;
			}

			.lang-dropdown {
				top: 100%;
				position: absolute;
				width: 100%;
				border: 1px solid #ccc;
			}

			.lang-main:hover {
				cursor: pointer;
			}

			.wpm-language-switcher:hover + .lang-dropdown {
				display: block;
			}

			.wpm-language-switcher .lang-dropdown {
				z-index: 1;
			}

			.wpm-language-switcher .lang-main img {
				top: 2px;
			}

			.wpm-language-switcher .lang-main,
			.lang-dropdown li a {
				line-height: 40px;
			}
		</style>
		<?php
	}
}
