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
	 */
	public function add_language_switcher() {

		$languages = wpm_get_languages();
		if ( count( $languages ) <= 1 ) {
			return;
		}

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'mega-menu_page_maxmegamenu_theme_editor' === $screen_id ) {
			wpm_enqueue_js( "
					(function ( $ ) {
					    if ($('#wpm-language-switcher').length === 0) {
					      var language_switcher = _.template(wpm_language_switcher_params.switcher);
					      $('#wpbody-content .megamenu_outer_wrap').first().prepend(language_switcher);
					    }
					})( window.jQuery );
				" );
		}
	}
}

new WPM_Megamenu();
