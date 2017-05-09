<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qtn_localize_url( $url, $new_locale = '' ) {
	global $locale, $qtn_config;

	$current_locale = get_locale();
	$locale = get_locale();

	if ( $new_locale ) {
		if ( ( $new_locale == $locale ) || ! isset( $qtn_config->languages[ $locale ] ) ) {
			return $url;
		}
		switch_to_locale( $new_locale );
	}

	$path = parse_url( $url, PHP_URL_PATH );
	if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
		$path = str_replace( $match[1], '/', $path );
	}

	$url    = home_url( $path );
	switch_to_locale( $current_locale );

	return $url;
}

function qtn_localize_text( $text, $new_locale = '' ) {
	global $qtn_config;

	if ( ! is_string( $text ) ) {
		return $text;
	}

	$strings = qtn_string_to_localize_array( $text );

	if ( empty( $strings ) ) {
		return $text;
	}

	$languages = $qtn_config->languages;

	if ( $new_locale ) {
		if ( isset( $strings[ $languages[ $new_locale ] ] ) ) {
			return  $strings[ $languages[ $new_locale ] ];
		} else {
			return '';
		}
	}

	if ( ! $new_locale && isset( $_GET['edit_lang'] ) ) {
		$lang = qtn_clean( $_GET['edit_lang'] );
		if ( isset( $strings[ $lang ] ) ) {
			return $strings[ $lang ];
		} else {
			return '';
		}
	}

	$locale = get_locale();

	if ( isset( $strings[ $languages[ $locale ] ] ) ) {
		return $strings[ $languages[ $locale ] ];
	} elseif ( isset( $strings[ $languages[ $qtn_config->default_locale ] ] ) ) {
		return $strings[ $languages[ $qtn_config->default_locale ] ];
	} else {
		return $text;
	}
}


function qtn_string_to_localize_array( $string ) {
	global $qtn_config;
	$result = array();

	if ( ! is_string( $string ) ) {
		return $result;
	}

	$string = htmlspecialchars_decode( $string );

	$split_regex = "#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism";
	$blocks      = preg_split( $split_regex, $string, - 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

	if ( empty( $blocks ) || count( $blocks ) == 1 ) {
		return $result;
	}

	foreach ($qtn_config->languages as $language) {
		$result[$language] = '';
	}

	$language = '';
	foreach ( $blocks as $block ) {

		if ( preg_match( "#^<!--:([a-z]{2})-->$#ism", $block, $matches ) ) {
			$language = $matches[1];
			continue;

		} elseif ( preg_match( "#^\[:([a-z]{2})\]$#ism", $block, $matches ) ) {
			$language = $matches[1];
			continue;

		} elseif ( preg_match( "#^\{:([a-z]{2})\}$#ism", $block, $matches ) ) {
			$language = $matches[1];
			continue;
		}

		switch ( $block ) {
			case '[:]':
			case '{:}':
			case '<!--:-->':
				$language = '';
				break;
			default:
				if ( $language ) {
					if ( isset( $result[ $language ] ) ) {
						$result[ $language ] .= $block;
					}
					$language = '';
				}
		}
	}

	foreach ( $result as $lang => $string ) {
		$result[ $lang ] = trim( $string );
	}

	return $result;
}

function qtn_localize_array_to_string( $strings ) {
	global $qtn_config;

	$string = '';

	if ( ! is_array( $strings ) ) {
		return $string;
	}

	foreach ( $strings as $key => $value ) {
		if ( in_array( $key, $qtn_config->languages) ) {
			$string .= '[:' . $key . ']' . trim( $value );
		}
	}

	$string .= '[:]';

	return $string;
}

function qtn_translate_object( $object, $locale = '' ) {

	foreach( get_object_vars( $object ) as $key => $content ) {
		switch( $key ){
			case 'post_title':
			case 'post_content':
			case 'post_excerpt':
			case 'name':
			case 'description':
				$object->$key = qtn_localize_text( $content, $locale );
				break;
		}
	}

	d($object);

	return $object;
}

function qtn_untranslate_post( $post ) {

	foreach( get_object_vars( $post ) as $key => $content ) {
		switch( $key ){
			case 'post_title':
			case 'post_content':
			case 'post_excerpt':
				$post->$key = get_post_field( $key, $post->ID, 'edit' );
				break;
		}
	}

	return $post;
}

function qtn_is_localize_string( $string ) {
	global $qtn_config;

	$strings = qtn_string_to_localize_array( $string );

	if ( is_array( $strings ) && ! empty( $strings ) ) {
		foreach ( $qtn_config->languages as $language ) {
			if ( isset( $strings[ $language ] ) ) {
				return true;
			}
		}
	}

	return false;
}
