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

		if ( null === $screen || ! $screen->post_type || ( $screen_id !== $screen->post_type ) || null === wpm_get_post_config( $screen->post_type ) || ! gutenberg_can_edit_post_type( $screen->post_type ) ) {
			return;
		}

		if ( count( wpm_get_languages() ) <= 1 ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher_customizer' );
		wpm_enqueue_js( "
			wp.api.init().then( function() {
				if ($('#wpm-language-switcher').length === 0) {
					var language_switcher = wp.template( 'wpm-ls-customizer' );
					$('.editor-header-toolbar').prepend(language_switcher);
			    }
		    });
	
		    $(document).on('click', '#wpm-language-switcher .lang-dropdown a', function(){
				var location = String(document.location);
				var lang = $(this).data('lang');
				var href = '';
				var query = location.split('?');
				var delimiter = '?';
				if (query[1]) {
					delimiter = '&';
				}
				if (query[1] && (query[1].search(/edit_lang=/i) !== -1)) {
					href = location.replace(/edit_lang=[a-z]{2,4}/i, 'edit_lang=' + lang);
				} else {
					href = location + delimiter + 'edit_lang=' + lang;
				}
				$(this).attr('href', href);
		    });
		");
	}
}
