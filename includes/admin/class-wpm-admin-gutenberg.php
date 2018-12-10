<?php
/**
 * Class for capability with Gutenberg editor
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Gutenberg {

	/**
	 * WPM_Gutenberg constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_language_switcher' ) );
	}


	/**
	 * Translate some field without PHP filters by javascript for displaying
	 */
	public function add_language_switcher() {
		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';

		if ( null === $screen || ! $screen->post_type || ( $screen_id !== $screen->post_type ) || null === wpm_get_post_config( $screen->post_type ) || ( function_exists( 'use_block_editor_for_post_type' ) && ! use_block_editor_for_post_type( $screen->post_type ) ) ) {
			return;
		}

		if ( count( wpm_get_languages() ) <= 1 ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher_customizer' );
		wp_enqueue_script( 'wp-api' );
		wp_add_inline_script( 'wp-api', "
(function( $ ) {
	$(window).on('pageshow',function(){
		wp.api.init().then( function() {
			if ($('#wpm-language-switcher').length === 0) {
				var language_switcher = wp.template( 'wpm-ls-customizer' );
				$('.edit-post-header-toolbar').prepend(language_switcher);
		    }
	    });
	});
	
	$(document).on('click', '#wpm-language-switcher .lang-dropdown a', function(){
		var lang = $(this).data('lang');
		var url = document.location.origin + document.location.pathname;
		var query = document.location.search;
		if (query.search(/edit_lang=/i) !== -1) {
			href = url + query.replace(/edit_lang=[a-z]{2,4}/i, 'edit_lang=' + lang) + document.location.hash;
		} else {
			href = url + query + '&edit_lang=' + lang + document.location.hash;
		}
		$(this).attr('href', href);
	});
})( jQuery );
");
	}
}
