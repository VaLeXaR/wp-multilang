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
				(function( $ ) {
				  'use strict';
				  
				  $(function() {
					wp.api.init().done( function() {
					if ($('#wpm-language-switcher').length === 0) {
					      var language_switcher = wp.template( 'wpm-ls' );
					      $('.components-panel__header h2').after(language_switcher);
					    }
				    });
				  });
				})( jQuery, wp );
			");

			add_action( 'admin_head', function () {
				?>
				<style>
					.wpm-language-switcher {
						position: relative;
						left: 0 !important;
						height: 100%;
					}

					.wpm-language-switcher .lang-main {
						padding-left: 10px;
						padding-right: 10px;
						line-height: 54px;
						height: 100%;
					}

					.lang-dropdown {
						position: absolute;
						top: 100%;
						left: 0;
						background-color: #fff;
						z-index: 1;
					}

					.lang-dropdown ul {
						list-style: none;
					}

					.lang-dropdown ul a {
						padding: 10px 10px;
						display: block;
					}

					.lang-dropdown ul a:hover, .lang-dropdown ul a:focus {
						background-color: #ccc;
					}
				</style>
				<?php
			} );
		}
	}

	new WPM_Gutenberg();
}
