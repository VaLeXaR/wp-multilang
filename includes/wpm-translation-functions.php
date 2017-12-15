<?php
/**
 * WPM Translation functions
 *
 * Functions for translation, set translations to multidimensional arrays.
 *
 * @author   Valentyn Riaboshtan
 * @category      Core
 * @package       WPM/Functions
 * @version       2.0.0
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

	$user_language = wpm_get_language();
	$options       = wpm_get_lang_option();

	if ( $language ) {
		if ( ( ( $language === $user_language ) && ( ! is_admin() || is_front_ajax() ) && ! isset( $_GET['lang'] ) ) || ! isset( $options[ $language ] ) ) {
			return $url;
		}
	} else {
		$language = $user_language;
	}

	if ( preg_match( '/^.*\.php$/i', wp_parse_url( $url, PHP_URL_PATH ) ) || ( strpos( $url, 'wp-admin/' ) !== false ) ) {
		return add_query_arg( 'lang', $language, $url );
	}

	$url         = remove_query_arg( 'lang', $url );
	$default_uri = str_replace( $host, '', $url );
	$default_uri = $default_uri ? $default_uri : '/';
	$pattern     = '!^/([a-z]{2})(/|$)!i';

	if ( preg_match( $pattern, $default_uri ) ) {
		$default_uri = preg_replace( $pattern, '/', $default_uri );
	}

	$default_language    = wpm_get_default_language();
	$default_lang_prefix = get_option( 'wpm_use_prefix', 'no' ) === 'yes' ? $default_language : '';

	if ( $language === $default_language ) {
		$new_uri = '/' . $default_lang_prefix . $default_uri;
	} else {
		$new_uri = '/' . $language . $default_uri;
	}

	$new_uri = preg_replace( '/(\/+)/', '/', $new_uri );

	if ( '/' != $new_uri ) {
		$new_url = $host . $new_uri;
	} else {
		$new_url = $host;
	}

	return apply_filters( 'wpm_translate_url', $new_url, $language, $url );
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

	if ( is_serialized_string( $string ) || json_decode( $string ) ) {
		return $string;
	}

	$strings = wpm_string_to_ml_array( $string );

	if ( ! is_array( $strings ) || empty( $strings ) ) {
		return $string;
	}

	if ( ! wpm_is_ml_array( $strings ) ) {
		return $strings;
	}

	$languages = wpm_get_languages();

	if ( $language ) {
		if ( isset( $languages[ $language ] ) ) {
			return $strings[ $language ];
		} else {
			return '';
		}
	}

	$language         = wpm_get_language();
	$default_language = wpm_get_default_language();

	if ( isset( $strings[ $language ] ) && ( '' == $strings[ $language ] ) && get_option( 'wpm_show_untranslated_strings', 'yes' ) === 'yes' ) {
		$default_text = apply_filters( 'wpm_untranslated_text', $strings[ $default_language ], $strings, $language );

		return $default_text;
	}

	if ( isset( $strings[ $language ] ) ) {
		return $strings[ $language ];
	}

	return '';
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

	if ( ! is_string( $string ) || is_serialized_string( $string ) || json_decode( $string ) ) {
		return $string;
	}

	$string = htmlspecialchars_decode( $string );
	$blocks = preg_split( '#(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\]|\{:[a-z]{2}\}|\{:\})#ism', $string, - 1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

	if ( empty( $blocks ) || count( $blocks ) === 1 ) {
		return $string;
	}

	$result = array();

	$languages = wpm_get_lang_option();

	foreach ( $languages as $key => $language ) {
		$result[ $key ] = '';
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

	foreach ( $result as $code => $string ) {
		$result[ $code ] = trim( $string );
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

	$languages = wpm_get_lang_option();
	foreach ( $strings as $key => $value ) {
		if ( isset( $languages[ $key ] ) && ( '' != $value ) ) {
			if ( wpm_is_ml_string( $value ) ) {
				$string = wpm_translate_string( $string );
			}
			$string .= '[:' . $key . ']' . trim( $value );
		}
	}

	if ( $string ) {
		$string .= '[:]';
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

	if ( ! $lang || ! isset( $languages[ $lang ] ) ) {
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
				foreach ( $languages as $lg => $language ) {
					$result[ $lg ] = '';
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

	foreach ( get_object_vars( $object ) as $key => $content ) {
		switch ( $key ) {
			case 'attr_title':
			case 'post_title':
			case 'name':
			case 'title':
				$object->$key = wpm_translate_string( $content, $lang );
				break;
			case 'post_excerpt':
			case 'description':
			case 'post_content':
				if ( is_serialized_string( $content ) ) {
					$object->$key = serialize( wpm_translate_value( unserialize( $content ), $lang ) );
					break;
				}

				if ( json_decode( $content ) ) {
					$object->$key = wp_json_encode( wpm_translate_value( json_decode( $content, true ), $lang ) );
					break;
				}

				if ( wpm_is_ml_string( $content ) ) {
					$object->$key = wpm_translate_string( $content, $lang );
					break;
				}
		}
	}

	return $object;
}


/**
 * Translate post
 *
 * @param $post
 *
 * @param string $lang
 *
 * @return object WP_Post
 */
function wpm_translate_post( $post, $lang = '' ) {

	if ( ! is_object( $post ) || is_null( wpm_get_post_config( $post->post_type ) ) ) {
		return $post;
	}

	return wpm_translate_object( $post, $lang );
}


/**
 * Translate term
 *
 * @param $term
 *
 * @param $taxonomy
 *
 * @param string $lang
 *
 * @return object WP_Term
 */
function wpm_translate_term( $term, $taxonomy, $lang = '' ) {

	if ( is_null( wpm_get_taxonomy_config( $taxonomy ) ) ) {
		return $term;
	}

	if ( is_object( $term ) ) {
		return wpm_translate_object( $term, $lang );
	}

	return wpm_translate_value( $term, $lang );

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
		global $wpdb;
		$orig_post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d;", $post->ID ) );
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

	$languages = wpm_get_lang_option();

	foreach ( $array as $key => $item ) {
		if ( ! isset( $languages[ $key ] ) ) {
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

	if ( is_array( $string ) || is_bool( $string ) || is_serialized_string( $string ) || json_decode( $string ) ) {
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

/**
 * Set new data to value
 *
 * @param $old_value
 * @param $new_value
 * @param array $config
 * @param string $lang
 *
 * @return array|bool|string
 */
function wpm_set_new_value( $old_value, $new_value, $config = array(), $lang = '' ) {

	if ( is_bool( $old_value ) || is_serialized_string( $old_value ) || json_decode( $old_value ) ) {
		return $old_value;
	}

	$old_value = wpm_value_to_ml_array( $old_value );
	$value     = wpm_set_language_value( $old_value, $new_value, $config, $lang );
	$value     = wpm_ml_value_to_string( $value );

	return $value;
}
