<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'document_title_parts', 'wpm_translate_value', 0 );

function wpm_set_meta_languages() {
	$languages = wpm_get_languages();
	$current_url = $_SERVER['REQUEST_URI'];
	foreach ( $languages as $language ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s"/>', $language, wpm_translate_url( $current_url, $language ) );
	}
}

add_action( 'wp_head', 'wpm_set_meta_languages', 0 );
