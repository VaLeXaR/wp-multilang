<?php
/**
 * Add 'lang' param to iframe url
 *
 * @author   Valentyn Riaboshtan
 * @category    Admin
 * @package     WPM/Includes/Admin
 * @class       WPM_Admin_Customizer
 * @since       1.4.9
 * @version     1.0.1
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Admin_Customizer
 */
class WPM_Admin_Customizer {

	/**
	 * WPM_Admin_Customizer constructor.
	 */
	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'add_lang_to_customizer_previewer' ) );
		add_action( 'customize_nav_menu_available_items', array( $this, 'load_available_items_query' ), 10, 3 );
	}

	/**
	 * Add script
	 */
	public function add_lang_to_customizer_previewer() {
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$src = wpm_asset_path( '/scripts/add-lang-to-customizer' . $suffix . '.js' );
		wp_enqueue_script( 'wpm-add-lang-to-customizer', $src, array( 'customize-controls' ), WPM_VERSION , true );
		$base_url = apply_filters( 'wpm_customizer_url', home_url() );
		$url = add_query_arg( 'lang', wpm_get_language(), $base_url );
		wp_add_inline_script( 'wpm-add-lang-to-customizer', sprintf( 'WPMLang.init( %s );', wp_json_encode( array( 'url' => $url ) ) ) );
	}

	public function load_available_items_query($items, $type, $object) {
		if (($type === 'post_type' && null !== wpm_get_post_config($object)) || ($type === 'taxonomy' && null !== wpm_get_taxonomy_config($object))) {
			foreach ($items as &$item) {
				$item['title'] = wpm_translate_string( $item['title'] );
			}
		}
		return $items;
	}
}
