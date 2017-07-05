<?php
/**
 * Add 'lang' param to iframe url
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     WPM/Core/Admin
 * @class       WPM_Admin_Customizer
 * @since       1.4.9
 */

namespace WPM\Core\Admin;

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
	}

	/**
	 * Add script
	 */
	public function add_lang_to_customizer_previewer() {
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$src = wpm_asset_path( '/scripts/add-lang-to-customizer' . $suffix . '.js' );
		wp_enqueue_script( 'wpm-add-lang-to-customizer', $src, array( 'customize-controls' ), WPM_VERSION , true );
		$url = add_query_arg( 'lang', wpm_get_language(), get_home_url() );
		$this->add_lang_to_template( $url );
	}

	/**
	 * Set the previewer url
	 *
	 * @param string $url
	 */
	public function add_lang_to_template( $url ) {
		wp_add_inline_script(
			'wpm-add-lang-to-customizer',
			sprintf( 'WPMLang.init( %s );', wp_json_encode( array( 'url' => $url ) ) ),
			'after'
		);
	}
}
