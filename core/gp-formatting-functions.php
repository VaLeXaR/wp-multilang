<?php
/**
 * GamePortal Formatting
 *
 * Functions for formatting data.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       GamePortal/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Sanitize taxonomy names. Slug format (no spaces, lowercase).
 *
 * urldecode is used to reverse munging of UTF8 characters.
 *
 * @param mixed $taxonomy
 *
 * @return string
 */
function gp_sanitize_taxonomy_name( $taxonomy ) {
	return apply_filters( 'sanitize_taxonomy_name', urldecode( sanitize_title( urldecode( $taxonomy ) ) ), $taxonomy );
}

/**
 * Sanitize permalink values before insertion into DB.
 *
 * Cannot use wc_clean because it sometimes strips % chars and breaks the user's setting.
 *
 * @param  string $value
 *
 * @return string
 */
function gp_sanitize_permalink( $value ) {
	global $wpdb;

	$value = $wpdb->strip_invalid_text_for_column( $wpdb->options, 'option_value', $value );

	if ( is_wp_error( $value ) ) {
		$value = '';
	}

	$value = esc_url_raw( $value );
	$value = str_replace( 'http://', '', $value );

	return untrailingslashit( $value );
}

/**
 * Gets the filename part of a download URL.
 *
 * @param string $file_url
 *
 * @return string
 */
function gp_get_filename_from_url( $file_url ) {
	$parts = parse_url( $file_url );
	if ( isset( $parts['path'] ) ) {
		return basename( $parts['path'] );
	}
}

/**
 * Convert a float to a string without locale formatting which PHP adds when changing floats to strings.
 *
 * @param  float $float
 *
 * @return string
 */
function gp_float_to_string( $float ) {
	if ( ! is_float( $float ) ) {
		return $float;
	}

	$locale = localeconv();
	$string = strval( $float );
	$string = str_replace( $locale['decimal_point'], '.', $string );

	return $string;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function gp_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'gp_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Sanitize a string destined to be a tooltip.
 *
 * @since 2.3.10 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
 *
 * @param string $var
 *
 * @return string
 */
function gp_sanitize_tooltip( $var ) {
	return htmlspecialchars( wp_kses( html_entity_decode( $var ), array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
	) ) );
}

/**
 * Merge two arrays.
 *
 * @param array $a1
 * @param array $a2
 *
 * @return array
 */
function gp_array_overlay( $a1, $a2 ) {
	foreach ( $a1 as $k => $v ) {
		if ( ! array_key_exists( $k, $a2 ) ) {
			continue;
		}
		if ( is_array( $v ) && is_array( $a2[ $k ] ) ) {
			$a1[ $k ] = gp_array_overlay( $v, $a2[ $k ] );
		} else {
			$a1[ $k ] = $a2[ $k ];
		}
	}

	return $a1;
}

/**
 * Format the price with a currency symbol.
 *
 * @param float $price
 * @param array $args (default: array())
 *
 * @return string
 */
function gp_price( $price ) {

	return print_payment_currency( $price, get_option( 'cart_currency_symbol' ), $decimal = '.' );
}

/**
 * let_to_num function.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @param $size
 *
 * @return int
 */
function gp_let_to_num( $size ) {
	$l   = substr( $size, - 1 );
	$ret = substr( $size, 0, - 1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}

	return $ret;
}

/**
 * Make a string lowercase.
 * Try to use mb_strtolower() when available.
 *
 * @param  string $string
 *
 * @return string
 */
function gp_strtolower( $string ) {
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string ) : strtolower( $string );
}

/**
 * Trim a string and append a suffix.
 *
 * @param  string  $string
 * @param  integer $chars
 * @param  string  $suffix
 *
 * @return string
 */
function gp_trim_string( $string, $chars = 200, $suffix = '...' ) {
	if ( strlen( $string ) > $chars ) {
		if ( function_exists( 'mb_substr' ) ) {
			$string = mb_substr( $string, 0, ( $chars - mb_strlen( $suffix ) ) ) . $suffix;
		} else {
			$string = substr( $string, 0, ( $chars - strlen( $suffix ) ) ) . $suffix;
		}
	}

	return $string;
}

/**
 * Format content to display shortcodes.
 *
 * @param  string $raw_string
 *
 * @return string
 */
function gp_format_content( $raw_string ) {
	return do_shortcode( shortcode_unautop( wpautop( $raw_string ) ) );
}

/**
 * Sanitize terms from an attribute text based.
 *
 * @param  string $term
 *
 * @return string
 */
function gp_sanitize_term_text_based( $term ) {
	return trim( wp_unslash( strip_tags( $term ) ) );
}

function gp_dateformat_PHP_to_jQueryUI( $php_format ) {
	$SYMBOLS_MATCHING = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => '',
		'A' => '',
		'B' => '',
		'g' => '',
		'G' => '',
		'h' => '',
		'H' => '',
		'i' => '',
		's' => '',
		'u' => ''
	);
	$jqueryui_format  = "";
	$escaping         = false;
	for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
		$char = $php_format[ $i ];
		if ( $char === '\\' ) // PHP date format escaping character
		{
			$i ++;
			if ( $escaping ) {
				$jqueryui_format .= $php_format[ $i ];
			} else {
				$jqueryui_format .= '\'' . $php_format[ $i ];
			}
			$escaping = true;
		} else {
			if ( $escaping ) {
				$jqueryui_format .= "'";
				$escaping = false;
			}
			if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
				$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
			} else {
				$jqueryui_format .= $char;
			}
		}
	}

	return $jqueryui_format;
}


function gp_field_date2mysql_date ($date) {
	$date_object = date_create_from_format( get_option('date_format'), esc_attr( $date ) );
	$mysql_date = date_format($date_object, 'Y-m-d H:i:s');
	return $mysql_date;
}
