<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpm_translate_url( $url, $new_locale = '' ) {

	$locale    = get_locale();
	$languages = wpm_get_languages();

	if ( $new_locale ) {
		if ( ( $new_locale == $locale ) || ! isset( $languages[ $new_locale ] ) ) {
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

function wpm_translate_string( $string, $locale = '' ) {

	$strings = wpm_string_to_ml_array( $string );

	if ( ! is_array( $strings ) ) {
		return $string;
	}

	if ( empty( $strings ) ) {
		return $string;
	}

	$languages = wpm_get_languages();

	if ( $locale ) {
		if ( isset( $strings[ $languages[ $locale ] ] ) ) {
			return $strings[ $languages[ $locale ] ];
		} else {
			return '';
		}
	}

	$lang = wpm_get_edit_lang();

	$default_locale = wpm_get_default_locale();

	if ( isset( $strings[ $lang ] ) ) {
		return $strings[ $lang ];
	} elseif ( isset( $strings[ $languages[ $default_locale ] ] ) ) {
		return $strings[ $languages[ $default_locale ] ];
	} else {
		return $string;
	}
}

function wpm_translate_value( $value, $locale = '' ) {
	if ( is_array( $value ) ) {
		$result = array();
		foreach ( $value as $k => $item ) {
			$result[ $k ] = wpm_translate_value( $item, $locale );
		}

		return $result;
	} elseif ( is_string( $value ) ) {
		return wpm_translate_string( $value, $locale );
	} else {
		return $value;
	}
}


function wpm_string_to_ml_array( $string ) {

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

	$languages = wpm_get_languages();

	foreach ( $languages as $language ) {
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


function wpm_value_to_ml_array( $value ) {
	if ( is_array( $value ) ) {
		$result = array();
		foreach ( $value as $k => $item ) {
			$result[ $k ] = wpm_value_to_ml_array( $item );
		}

		return $result;
	} elseif ( is_string( $value ) ) {
		return wpm_string_to_ml_array( $value );
	} else {
		return $value;
	}
}

function wpm_ml_array_to_string( $strings ) {

	$string = '';

	if ( ! is_array( $strings ) || ! wpm_is_ml_array( $strings ) ) {
		return $string;
	}

	$languages = wpm_get_languages();
	foreach ( $strings as $key => $value ) {
		if ( in_array( $key, $languages ) && ! empty( $value ) ) {
			if ( wpm_is_ml_string( $value ) ) {
				$string = wpm_translate_string( $string );
			}
			$string .= '[:' . $key . ']' . trim( $value );
		}
	}

	if ( ! $string ) {
		return '';
	}

	$string .= '[:]';

	return $string;
}


function wpm_ml_value_to_string( $value ) {

	if ( is_array( $value ) ) {
		if ( wpm_is_ml_array( $value ) ) {
			return wpm_ml_array_to_string( $value );
		} else {
			$result = array();
			foreach ( $value as $key => $item ) {
				$result[ $key ] = wpm_ml_value_to_string( $item );
			}

			return $result;
		}
	} else {
		return $value;
	}
}


function wpm_set_language_value( $localize_array, $value, $config = null, $locale = '' ) {
	$languages = wpm_get_languages();
	$lang      = wpm_get_edit_lang();

	if ( isset( $_POST['lang'] ) && in_array( $_POST['lang'], $languages ) ) {
		$lang = wpm_clean( $_POST['lang'] );
	}

	if ( $locale && isset( $languages[ $locale ] ) ) {
		$lang = $languages[ $locale ];
	}

	if ( is_array( $value ) && ! is_null( $config ) ) {
		foreach ( $value as $key => $item ) {
			if ( isset( $config['wpm_each'] ) ) {
				$config = $config['wpm_each'];
			} else {
				$config = ( isset( $config[ $key ] ) ? $config[ $key ] : null );
			}
			$localize_array[ $key ] = wpm_set_language_value( $localize_array[ $key ], $value[ $key ], $config, $locale );
		}
	} else {
		if ( ! is_null( $config ) ) {
			if ( wpm_is_ml_array( $localize_array ) ) {
				$localize_array[ $lang ] = $value;
			} else {
				$result = array();
				foreach ( $languages as $language ) {
					$result[ $language ] = '';
				}
				$result[ $lang ] = $value;
				$localize_array  = $result;
			}
		} else {
			$localize_array = $value;
		}
	}

	return $localize_array;
}

function wpm_translate_object( $object, $locale = '' ) {

	if ( $object instanceof WP_Post || $object instanceof WP_Term ) {

		foreach ( get_object_vars( $object ) as $key => $content ) {
			switch ( $key ) {
				case 'attr_title':
				case 'post_title':
				case 'post_excerpt':
				case 'name':
				case 'title':
				case 'description':
					$object->$key = wpm_translate_string( $content, $locale );
					break;
				case 'post_content':
					$object->$key = wpm_translate_value( $content, $locale );
					break;
			}
		}
	}

	return $object;
}

function wpm_untranslate_post( $post ) {

	if ( $post instanceof WP_Post ) {

		foreach ( get_object_vars( $post ) as $key => $content ) {
			switch ( $key ) {
				case 'post_title':
				case 'post_content':
				case 'post_excerpt':
					$post->$key = get_post_field( $key, $post->ID, 'edit' );
					break;
			}
		}
	}

	return $post;
}

function wpm_is_ml_array( $array ) {

	if ( ! is_array( $array ) ) {
		return false;
	}

	$languages = wpm_get_languages();

	foreach ( $array as $key => $item ) {
		if ( ! in_array( $key, $languages ) ) {
			return false;
		}
	}

	return true;
}

function wpm_is_ml_string( $string ) {

	if ( ! is_string( $string ) ) {
		return false;
	}

	$strings = wpm_string_to_ml_array( $string );

	if ( is_array( $strings ) && ! empty( $strings ) ) {
		return true;
	}

	return false;
}

function wpm_is_ml_value( $value ) {

	if ( is_array( $value ) ) {
		$result = array_filter( $value, 'wpm_is_ml_array' );
		if ( $result ) {
			return true;
		}

		return false;
	} else {
		return wpm_is_ml_string( $value );
	}
}
