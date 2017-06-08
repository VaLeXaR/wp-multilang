<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'MC4WP_VERSION' ) ) {

	/**
	 * Class WPM_MailChimp_For_WP
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @since    1.2.0
	 */
	class WPM_MailChimp_For_WP {

		/**
		 * WPM_MailChimp_For_WP constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_translator_script' ), 11 );
			add_filter( 'mc4wp_form_content', 'wpm_translate_string' );
			add_filter( "mc4wp_integration_checkbox_label", 'wpm_translate_string' );
		}


		/**
		 * Translate some field without PHP filters by javascript for displaying
		 */
		public function add_translator_script() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( $screen_id == 'mailchimp-for-wp_page_mailchimp-for-wp-integrations' && isset( $_GET['integration'] ) ) {
				wp_enqueue_script( 'wpm_translator' );
				wpm_enqueue_js( "
					(function ( $ ) {
						$( '#mc4wp_checkbox_label' ).each( function () {
							var text = wpm_translator.translate_string($(this).val());
							$(this).val(text);
						} );
					})( window.jQuery );
				" );
			}

			if ( $screen_id == 'mailchimp-for-wp_page_mailchimp-for-wp-forms' && isset( $_GET['form_id'] ) ) {
				wp_enqueue_script( 'wpm_translator' );
				wpm_enqueue_js( "
					(function ( $ ) {
						$( '#mc4wp-form-content' ).each( function () {
							var text = wpm_translator.translate_string($(this).val());
							$(this).val(text);
						} );
						
						console.log(window.mc4wp.forms.editor.refresh());
					})( window.jQuery );
				" );
			}
		}
	}

	new WPM_MailChimp_For_WP();
}
