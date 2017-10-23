<?php
/**
 * Class for capability with Gutenberg editor
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GUTENBERG_VERSION' ) ) {
	return;
}

/**
 * Class WPM_Gutenberg
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since 1.4.11
 * @version 1.1.0
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
	 *
	 * @param $hook
	 */
	public function add_language_switcher( $hook ) {

		if ( count( wpm_get_languages() ) <= 1 || ! preg_match( '/(toplevel|gutenberg)_page_gutenberg(-demo)?/', $hook, $page_match ) ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', function () {
			echo wpm_get_template_html( 'language-switcher-customizer.php' );
		} );

		wp_add_inline_script( 'wp-editor', "
			(function( $ ) {
			  'use strict';
			  
			  $(function() {
				wp.api.init().done( function() {
				if ($('#wpm-language-switcher').length === 0) {
				      var language_switcher = wp.template( 'wpm-ls' );
				      $('.editor-header__settings > .editor-publish-button').before(language_switcher);
				    }
			    });
			    
			    $(document).on('click', '#wpm-language-switcher .lang-dropdown a', function(){
			        var post_id = getURLVar('post_id', $(location).attr('href')),
			            switch_lang_url = $(this).attr('href');
			        if (post_id && !getURLVar('post_id', switch_lang_url)) {
			            $(this).attr('href', switch_lang_url + '&post_id=' + post_id);
			        }
			    });
			    
			    function getURLVar(key, url) {
					var value = [];
				
					var query = String(url).split('?');
				
					if (query[1]) {
						var part = query[1].split('&');
				
						for (var i = 0; i < part.length; i++) {
							var data = part[i].split('=');
				
							if (data[0] && data[1]) {
								value[data[0]] = data[1];
							}
						}
				
						if (value[key]) {
							return value[key];
						} else {
							return '';
						}
					}
				}
			  });
			})( jQuery, wp );
		");
	}
}

new WPM_Gutenberg();
