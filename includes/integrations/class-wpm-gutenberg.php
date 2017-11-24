<?php
/**
 * Class for capability with Gutenberg editor
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Gutenberg
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Gutenberg {

	/**
	 * WPM_Gutenberg constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_language_switcher' ), 11 );
	}


	/**
	 * Translate some field without PHP filters by javascript for displaying
	 */
	public function add_language_switcher() {
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';

		if ( is_null( $screen ) || ! $screen->post_type || is_null( wpm_get_post_config( $screen->post_type ) ) || ( $screen_id !== $screen->post_type ) ) {
			return;
		}

		if ( count( wpm_get_languages() ) <= 1 ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher_customizer' );
		wp_enqueue_script( 'wpm_translator' );

		wpm_enqueue_js( "
			wp.api.init().done( function() {
				if ($('#wpm-language-switcher').length === 0) {
			      var language_switcher = wp.template( 'wpm-ls-customizer' );
			      $('.editor-header-toolbar').prepend(language_switcher);
			      
			      var languages = wpm_translator_params.languages;
			          i = languages.indexOf(wpm_translator_params.language);
			      if (i >= 0) {
				      languages.splice( i, 1 );
				  }
				  
				  var location = String(document.location);
				  var query = location.split('?');
				  var delimiter = '?';
				  if (query[1]) {
				    delimiter = '&';
				  }
			      
			      $('#wpm-language-switcher .lang-dropdown a').each(function(i){
			        $(this).attr('href', location + delimiter + 'edit_lang=' + languages[i]);
			      });
			    }
		    });
		");
	}
}
