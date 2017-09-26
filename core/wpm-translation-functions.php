<?php
/**
 * WPM Translation functions
 *
 * Functions for translation, set translations to multidimensional arrays.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
 * @version       1.0.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translate url
 *
 * @param string $url
 * @param string $language
 *
 * @return string
 */
function wpm_translate_url( $url, $language = '' ) {

	$host = wpm_get_orig_home_url();

	if ( strpos( $url, $host ) === false ) {
		return $url;
	}

	$locale         = get_locale();
	$default_locale = wpm_get_default_locale();
	$languages      = wpm_get_languages();

	if ( $language ) {
		if ( ( $language === $languages[ $locale ] ) || ! in_array( $language, $languages, true ) ) {
			return $url;
		}
	}

	$url_lang = '';
	$path     = wpm_get_site_request_uri();

	if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
		$url_lang = $match[1];
	}

	$new_path = '';

	if ( $language ) {

		if ( ! $url_lang && ( $language === $languages[ $default_locale ] ) ) {
			return $url;
		} elseif ( $url_lang && ( $language === $languages[ $default_locale ] ) ) {
			$new_path = str_replace( '/' . $url_lang . '/', '/', $path );
		} elseif ( $url_lang && ( $language !== $languages[ $default_locale ] ) ) {
			$new_path = str_replace( '/' . $url_lang . '/', '/' . $language . '/', $path );
		} elseif ( ! $url_lang && ( $path !== $languages[ $default_locale ] ) ) {
			$new_path = '/' . $language . $path;
		}
	} else {
		if ( ! $url_lang && ( $locale === $default_locale ) ) {
			return $url;
		} elseif ( ! $url_lang && ( $locale !== $default_locale ) ) {
			$new_path = '/' . $languages[ $locale ] . $path;
		} elseif ( $url_lang && ( $locale === $default_locale ) ) {
			$new_path = str_replace( '/' . $url_lang . '/', '/', $path );
		} elseif ( $url_lang && ( $locale !== $default_locale ) ) {
			$new_path = str_replace( '/' . $url_lang . '/', '/' . $languages[ $locale ] . '/', $path );
		}
	}

	if ( $new_path ) {
		$url = str_replace( $host . $path, $host . $new_path, $url );
	}

	return $url;
}

/**
 * Translate multilingual string
 *
 * @param string $string
 * @param string $language
 *
 * @return array|mixed|string
 */
function wpm_translate_string( $string, $language = '' ) {

	$strings = wpm_string_to_ml_array( $string );

	if ( ! is_array( $strings ) || empty( $strings ) ) {
		return $string;
	}

	if ( ! wpm_is_ml_array( $strings ) ) {
		return $strings;
	}

	$languages = wpm_get_languages();

	if ( $language ) {
		if ( in_array( $language, $languages, true ) ) {
			return $strings[ $language ];
		} else {
			return '';
		}
	}

	$language       = wpm_get_language();
	$default_locale = wpm_get_default_locale();

	if ( ( '' == $strings[ $language ] ) && get_option( 'wpm_show_untranslated_strings' ) ) {
		return $strings[ $languages[ $default_locale ] ];
	}

	return $strings[ $language ];
}

/**
 * Translate multidimensional array with multilingual strings
 *
 * @param        $value
 * @param string $language
 *
 * @return array|mixed|string
 */
function wpm_translate_value( $value, $language = '' ) {
	if ( is_array( $value ) ) {
		$result = array();
		foreach ( $value as $k => $item ) {
			$result[ $k ] = wpm_translate_value( $item, $language );
		}

		return $result;
	} elseif ( is_string( $value ) ) {
		return wpm_translate_string( $value, $language );
	} else {
		return $value;
	}
}

/**
 * Transform multilingual string to multilingual array
 *
 * @param $string
 *
 * @return array|mixed|string
 */
function wpm_string_to_ml_array( $string ) {

	if ( ! is_string( $string ) ) {
		return $string;
	}

	$string = htmlspecialchars_decode( $string );

	$split_regex = '#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism';
	$blocks      = preg_split( $split_regex, $string, - 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

	if ( empty( $blocks ) || count( $blocks ) === 1 ) {
		return $string;
	}

	$result = array();

	$languages = wpm_get_all_languages();

	foreach ( $languages as $language ) {
		$result[ $language ] = '';
	}

	$language = '';
	foreach ( $blocks as $block ) {

		if ( preg_match( '#^<!--:([a-z]{2})-->$#ism', $block, $matches ) ) {
			$language = $matches[1];
			continue;

		} elseif ( preg_match( '#^\[:([a-z]{2})\]$#ism', $block, $matches ) ) {
			$language = $matches[1];
			continue;

		} elseif ( preg_match( '#^\{:([a-z]{2})\}$#ism', $block, $matches ) ) {
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

/**
 * Transform multidimensional array with multilingual strings to multidimensional array with multilingual arrays
 *
 * @param $value
 *
 * @return array|mixed|string
 */
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

/**
 * Transform multilingual array to multilingual string
 *
 * @param $strings
 *
 * @return string
 */
function wpm_ml_array_to_string( $strings ) {

	$string = '';

	if ( ! wpm_is_ml_array( $strings ) ) {
		return $string;
	}

	$languages = wpm_get_all_languages();
	foreach ( $strings as $key => $value ) {
		if ( in_array( $key, $languages, true ) && ( '' != $value ) ) {
			if ( wpm_is_ml_string( $value ) ) {
				$string = wpm_translate_string( $string );
			}
			$string .= '[:' . $key . ']' . trim( $value );
		}
	}

	return $string;
}

/**
 * Transform multidimensional array with multilingual arrays to multidimensional array with multilingual strings
 *
 * @param $value
 *
 * @return array|string
 */
function wpm_ml_value_to_string( $value ) {

	if ( is_array( $value ) && ! empty( $value ) ) {

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

/**
 * Set new value to multidimensional array with multilingual arrays by config
 *
 * @param        $localize_array
 * @param mixed  $value
 * @param array  $config
 * @param string $lang
 *
 * @return array|bool
 */
function wpm_set_language_value( $localize_array, $value, $config = array(), $lang = '' ) {
	$languages = wpm_get_languages();
	$new_value = array();

	if ( ! $lang || ! in_array( $lang, $languages, true ) ) {
		$lang = wpm_get_language();
	}

	if ( is_array( $value ) && ! is_null( $config ) ) {
		foreach ( $value as $key => $item ) {
			if ( isset( $config['wpm_each'] ) ) {
				$config_key = $config['wpm_each'];
			} else {
				$config_key = ( isset( $config[ $key ] ) ? $config[ $key ] : null );
			}

			if ( ! isset( $localize_array[ $key ] ) ) {
				$localize_array[ $key ] = array();
			}

			$new_value[ $key ] = wpm_set_language_value( $localize_array[ $key ], $value[ $key ], $config_key, $lang );
		}
	} else {
		if ( ! is_null( $config ) && ! is_bool( $value ) ) {

			if ( wpm_is_ml_string( $value ) ) {
				$value = wpm_translate_string( $value );
			}

			if ( wpm_is_ml_array( $localize_array ) ) {
				$new_value = $localize_array;
				$new_value[ $lang ] = $value;
			} else {
				$result = array();
				foreach ( $languages as $language ) {
					$result[ $language ] = '';
				}
				$result[ $lang ] = $value;
				$new_value  = $result;
			}
		} else {
			$new_value = $value;
		}
	}// End if().

	return $new_value;
}


/**
 * Translate WP object
 *
 * @param        $object
 * @param string $lang
 *
 * @return mixed
 */
function wpm_translate_object( $object, $lang = '' ) {

	if ( $object instanceof WP_Post || $object instanceof WP_Term ) {

		foreach ( get_object_vars( $object ) as $key => $content ) {
			switch ( $key ) {
				case 'attr_title':
				case 'post_title':
				case 'post_excerpt':
				case 'name':
				case 'title':
				case 'description':
					$object->$key = wpm_translate_string( $content, $lang );
					break;
				case 'post_content':
					$object->$key = maybe_serialize( wpm_translate_value( maybe_unserialize( $content ), $lang ) );
					break;
			}
		}
	}

	return $object;
}

/**
 * Untranslate WP_Post object
 *
 * @param $post
 *
 * @return mixed
 */
function wpm_untranslate_post( $post ) {

	if ( $post instanceof WP_Post ) {

		$orig_post = get_post( $post->ID );

		foreach ( get_object_vars( $post ) as $key => $content ) {
			switch ( $key ) {
				case 'post_title':
				case 'post_content':
				case 'post_excerpt':
					$post->$key = $orig_post->$key;
					break;
			}
		}
	}

	return $post;
}

/**
 * Check if array is multilingual
 *
 * @param $array
 *
 * @return bool
 */
function wpm_is_ml_array( $array ) {

	if ( ! is_array( $array ) || wp_is_numeric_array( $array ) ) {
		return false;
	}

	$languages = wpm_get_all_languages();

	foreach ( $array as $key => $item ) {
		if ( ! in_array( $key, $languages, true ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Check if string is multilingual
 *
 * @param $string
 *
 * @return bool
 */
function wpm_is_ml_string( $string ) {

	if ( is_array( $string ) || is_bool( $string ) ) {
		return false;
	}

	$strings = wpm_string_to_ml_array( $string );

	if ( is_array( $strings ) && ! empty( $strings ) ) {
		return true;
	}

	return false;
}

/**
 * Check if value with multilingual strings
 *
 * @param $value
 *
 * @return bool
 */
function wpm_is_ml_value( $value ) {

	if ( is_array( $value ) && ! empty( $value ) ) {
		$result = array();
		foreach ( $value as $item ) {
			$result[] = wpm_is_ml_value( $item );
		}

		if ( in_array( true, $result, true ) ) {
			return true;
		}

		return false;
	} else {
		return wpm_is_ml_string( $value );
	}
}
