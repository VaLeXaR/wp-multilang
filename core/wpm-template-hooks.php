<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'document_title_parts', 'wpm_translate_value', 0 );

function wpm_set_meta_languages() {
	global $wp;
	$languages = wpm_get_languages();
	$current_url = home_url( $wp->request );
	foreach ( $languages as $locale => $language ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s"/>', $language, wpm_translate_url( $current_url, $locale ) );
	}
}

add_action( 'wp_head', 'wpm_set_meta_languages', 0 );
