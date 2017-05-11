<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function qtn_translate_url( $url, $new_locale = '' ) {
	global $qtn_config;

	$locale = get_locale();

	if ( $new_locale ) {
		if ( ( $new_locale == $locale ) || ! isset( $qtn_config->languages[ $new_locale ] ) ) {
			return $url;
		}
		switch_to_locale( $new_locale );
	}

	$path = parse_url( $url, PHP_URL_PATH );
	if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
		$path = str_replace( $match[1], '/', $path );
	}

	$url = home_url( $path );
	switch_to_locale( $locale );

	return $url;
}

function qtn_translate_string( $string, $locale = '' ) {
	global $qtn_config;

	$strings = qtn_string_to_ml_array( $string );

	if ( empty( $strings ) ) {
		return $string;
	}

	$languages = $qtn_config->languages;

	if ( $locale ) {
		if ( isset( $strings[ $languages[ $locale ] ] ) ) {
			return $strings[ $languages[ $locale ] ];
		} else {
			return '';
		}
	}

	$lang = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : $languages[ get_locale() ];

	if ( isset( $strings[ $lang ] ) ) {
		return $strings[ $lang ];
	} elseif ( isset( $strings[ $languages[ $qtn_config->default_locale ] ] ) ) {
		return $strings[ $languages[ $qtn_config->default_locale ] ];
	} else {
		return $string;
	}
}

function qtn_translate_value( $value, $locale = '' ) {
	if ( is_array( $value ) ) {
		$result = array();
		foreach ( $value as $k => $item ) {
			$result[ $k ] = qtn_translate_value( $item, $locale );
		}

		return $result;
	} elseif ( is_string( $value ) ) {
		return qtn_translate_string( $value, $locale );
	} else {
		return $value;
	}
}


function qtn_string_to_ml_array( $string ) {
	global $qtn_config;

	if ( ! is_string( $string ) ) {
		return $string;
	}

	$string = htmlspecialchars_decode( $string );

	$split_regex = "#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism";
	$blocks      = preg_split( $split_regex, $string, - 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

	if ( empty( $blocks ) || count( $blocks ) == 1 ) {
		return $string;
	}

	$result = array();

	foreach ( $qtn_config->languages as $language ) {
		$result[ $language ] = '';
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

function qtn_value_to_ml_array( $value ) {
	if ( is_array( $value ) ) {
		$result = array();
		foreach ( $value as $k => $item ) {
			$result[ $k ] = qtn_value_to_ml_array( $item );
		}

		return $result;
	} elseif ( is_string( $value ) ) {
		return qtn_string_to_ml_array( $value );
	} else {
		return $value;
	}
}

function qtn_ml_array_to_string( $strings ) {
	global $qtn_config;

	$string = '';

	if ( ! is_array( $strings ) || ! qtn_is_ml_array( $strings ) ) {
		return $string;
	}

	foreach ( $strings as $key => $value ) {
		if ( in_array( $key, $qtn_config->languages ) ) {
			$string .= '[:' . $key . ']' . trim( $value );
		}
	}

	$string .= '[:]';

	return $string;
}

function qtn_ml_value_to_string( $value ) {

	if ( is_array( $value ) ) {
		if ( qtn_is_ml_array( $value ) ) {
			return qtn_ml_array_to_string( $value );
		} else {
			$result = array();
			foreach ( $value as $key => $item ) {
				$result[ $key ] = qtn_ml_value_to_string( $item );
			}

			return $result;
		}
	} else {
		return $value;
	}
}

function qtn_set_language_value( $localize_array, $value, $locale = '' ) {
	global $qtn_config;
	$lang = isset( $_POST['lang'] ) ? qtn_clean( $_POST['lang'] ) : $qtn_config->languages[ get_locale() ];

	if ( $locale && isset( $qtn_config->languages[ $locale ] ) ) {
		$lang = $qtn_config->languages[ $locale ];
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$localize_array[ $key ] = qtn_set_language_value( $localize_array[ $key ], $value[ $key ], $locale );
		}
	} else {
		if ( is_string( $value ) ) {
			if ( qtn_is_ml_array( $localize_array ) ) {
				$localize_array[ $lang ] = $value;
			} else {
				$result = array();
				foreach ( $qtn_config->languages as $language ) {
					$result[ $language ] = '';
				}
				$result[ $lang ] = $value;
				$localize_array = $result;
			}
		} else {
			$localize_array = $value;
		}
	}

	return $localize_array;
}

function qtn_translate_object( $object, $locale = '' ) {

	if ( is_object( $object) && ($object instanceof WP_Post || $object instanceof WP_Term)) {

		foreach ( get_object_vars( $object ) as $key => $content ) {
			switch ( $key ) {
				case 'post_title':
				case 'post_content':
				case 'post_excerpt':
				case 'name':
				case 'description':
					$object->$key = qtn_translate_value( $content, $locale );
					break;
			}
		}

		return $object;
	}

	return $object;
}

function qtn_untranslate_post( $post ) {

	foreach ( get_object_vars( $post ) as $key => $content ) {
		switch ( $key ) {
			case 'post_title':
			case 'post_content':
			case 'post_excerpt':
				$post->$key = get_post_field( $key, $post->ID, 'edit' );
				break;
		}
	}

	return $post;
}

function qtn_is_ml_array( $array ) {
	global $qtn_config;

	if ( ! is_array( $array) ) {
		return false;
	}

	foreach ( $array as $key => $item ) {
		if ( ! in_array( $key, $qtn_config->languages ) ) {
			return false;
		}
	}

	return true;
}

function qtn_is_ml_string( $string ) {

	if ( ! is_string( $string ) ) {
		return false;
	}

	$strings = qtn_string_to_ml_array( $string );

	if ( is_array( $strings ) && ! empty( $strings ) ) {
		return true;
	}

	return false;
}

function qtn_is_ml_value( $value ) {

	if ( is_array( $value ) ) {
		$result = array_filter( $value, 'qtn_is_ml_array' );
		if ( $result ) {
			return true;
		}

		return false;
	} else {
		return qtn_is_ml_string( $value );
	}
}
