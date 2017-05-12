<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qtn_setup_lang_query() {
	$user_language = qtn_get_user_language();
	set_query_var( 'lang', $user_language );
	add_filter( 'request', function ( $query_vars ) {
		$query_vars['lang'] = get_query_var( 'lang' );

		return $query_vars;
	} );
}

add_action( 'after_setup_theme', 'qtn_setup_lang_query', 0 );


function qtn_change_locale( $new_locale ) {
	global $locale;
	$locale = $new_locale;
}

add_action( 'change_locale', 'qtn_change_locale', 0 );


function qtn_set_home_url( $value ) {
	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || defined( 'REST_REQUEST' ) ) {
		return $value;
	}

	//TODO set cookie for ajax

	$locale    = get_locale();
	$languages = qtn_get_languages();
	$default_locale = qtn_get_default_locale();
	if ( $languages[ $locale ] != $languages[ $default_locale ] ) {
		$value .= '/' . $languages[ $locale ];
	}

	return $value;
}

add_filter( 'option_home', 'qtn_set_home_url', 0 );




function qtn_set_lang_var( $public_query_vars ) {
	$public_query_vars[] = 'lang';

	return $public_query_vars;
}

add_filter( 'query_vars', 'qtn_set_lang_var' );



//add_action( 'after_setup_theme', array( $this, 'set_settings' ), 0 );
