<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qtn_localize_url( $url, $new_locale = '' ) {
	global $locale, $qtn_config;

	$current_locale = $locale;

	if ( $new_locale ) {
		if ( ( $new_locale == $locale ) || ! isset( $qtn_config->languages[ $locale ] ) ) {
			return $url;
		}
		$locale = $new_locale;
	}

	$path = parse_url( $url, PHP_URL_PATH );
	if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
		$path = str_replace( $match[1], '/', $path );
	}

	$url    = home_url( $path );
	$locale = $current_locale;

	return $url;
}

function qtn_localize_text( $text, $new_locale = '' ) {
	global $qtn_config;

	$locale = get_locale();
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

	if ( isset( $strings[ $languages[ $locale ] ] ) ) {
		return $strings[ $languages[ $locale ] ];
	} elseif ( isset( $strings[ $languages[ $qtn_config->default_lang ] ] ) ) {
		return $strings[ $languages[ $qtn_config->default_lang ] ];
	} else {
		return $text;
	}
}


function qtn_string_to_localize_array( $string ) {
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
					$result[ $language ] = $block;
					$language            = '';
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

function qtn_translate_post( $post, $locale = '' ) {

	if ( ! $locale) {
		$locale = get_locale();
	}

	foreach( get_object_vars( $post ) as $key => $content ) {
		switch( $key ){
			case 'post_title':
			case 'post_content':
			case 'post_excerpt':
				$post->$key = qtn_localize_text( $content, $locale );
				break;
		}
	}

	return $post;
}
