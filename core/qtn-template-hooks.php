<?php

add_filter( 'document_title_parts', 'qtn_translate_value', 0 );

function qtn_set_meta_languages() {
	global $wp;
	$current_url = home_url( $wp->request );
	foreach ( $this->languages as $locale => $language ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s"/>', $language, qtn_translate_url( $current_url, $locale ) );
	}
}

add_action( 'wp_head', array( $this, 'qtn_set_meta_languages' ) );
