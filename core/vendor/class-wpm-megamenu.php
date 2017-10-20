<?php
/**
 * Class for capability with Max Mega Menu
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MEGAMENU_VERSION' ) ) {
	return;
}

/**
 * Class WPM_Megamenu
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.3.0
 */
class WPM_Megamenu {

	/**
	 * WPM_Megamenu constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_language_switcher' ), 11 );
	}


	/**
	 * Add language switcher
	 *
	 * @param $hook
	 */
	public function add_language_switcher( $hook ) {

		if ( count( wpm_get_languages() ) <= 1 || ( 'mega-menu_page_maxmegamenu_theme_editor' !== $hook ) ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', function () {
			echo wpm_get_template_html( 'language-switcher.php' );
		} );

		wpm_enqueue_js( "
			(function ( $ ) {
			    if ($('#wpm-language-switcher').length === 0) {
					var language_switcher = wp.template( 'wpm-ls' );
					$('#wpbody-content .megamenu_outer_wrap').first().prepend(language_switcher);
			    }
			})( window.jQuery );
		" );
	}
}

new WPM_Megamenu();
