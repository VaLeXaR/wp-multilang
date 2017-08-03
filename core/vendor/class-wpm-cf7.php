<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPCF7_PLUGIN' ) ) {
	return;
}

/**
 * Class WPM_CF7
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.2.0
 */
class WPM_CF7 {

	/**
	 * WPM_CF7 constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_translator_script' ), 11 );
	}


	/**
	 * Translate some field without PHP filters by javascript for displaying
	 */
	public function add_translator_script() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( $screen_id === 'toplevel_page_wpcf7' && isset( $_GET['post'] ) ) {
			wp_enqueue_script( 'wpm_translator' );
			wpm_enqueue_js( "
					(function ( $ ) {
						$( '#title' ).each( function () {
							var text = wpm_translator.translate_string($(this).val());
							$(this).val(text);
						} );
					})( window.jQuery );
				" );
		}
	}
}

new WPM_CF7();
