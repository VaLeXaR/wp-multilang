<?php
/**
 * Class for capability with Gutenberg editor
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'GUTENBERG_VERSION' ) ) {

	/**
	 * Class WPM_Gutenberg
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 */
	class WPM_Gutenberg {

		/**
		 * WPM_CF7 constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'add_language_switcher' ), 11 );
		}


		/**
		 * Translate some field without PHP filters by javascript for displaying
		 */
		public function add_language_switcher( $hook ) {

			if ( ! preg_match( '/(toplevel|gutenberg)_page_gutenberg(-demo)?/', $hook, $page_match ) ) {
				return;
			}

			add_action( 'admin_print_footer_scripts', function () {
				echo wpm_get_template_html( 'language-switcher-customizer.php' );
			} );

			wp_add_inline_script(
				'wp-editor', "
				(function ($) {
				  'use strict';
				
				  $(function () {
				
				    if ($('#wpm-language-switcher').length === 0) {
				      var language_switcher = _.template($('#tmpl-wpm-ls').text());
				      $('#components-panel__header р2').after(language_switcher);
				    }
				
				  });
				})(jQuery);
			");

			wpm_enqueue_js( "
				(function ($) {
				  'use strict';
				
				  $(function () {
				
				    if ($('#wpm-language-switcher').length === 0) {
				      var language_switcher = _.template($('#tmpl-wpm-ls').text());
				      $('#gutenberg__editor').after(language_switcher);
				    }
				
				  });
				})(jQuery);
			" );
		}
	}

//	new WPM_Gutenberg();
}
