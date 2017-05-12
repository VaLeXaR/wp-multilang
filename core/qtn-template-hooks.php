<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'document_title_parts', 'qtn_translate_value', 0 );

function qtn_set_meta_languages() {
	global $wp;
	$languages = qtn_get_languages();
	$current_url = home_url( $wp->request );
	foreach ( $languages as $locale => $language ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s"/>', $language, qtn_translate_url( $current_url, $locale ) );
	}
}

add_action( 'wp_head', 'qtn_set_meta_languages', 0 );
