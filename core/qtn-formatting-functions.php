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
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var
 *
 * @return string|array
 */
function qtn_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'qtn_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Merge two arrays.
 *
 * @param array $a1
 * @param array $a2
 *
 * @return array
 */
function qtn_array_overlay( $a1, $a2 ) {
	foreach ( $a1 as $k => $v ) {
		if ( ! array_key_exists( $k, $a2 ) ) {
			continue;
		}
		if ( is_array( $v ) && is_array( $a2[ $k ] ) ) {
			$a1[ $k ] = qtn_array_overlay( $v, $a2[ $k ] );
		} else {
			$a1[ $k ] = $a2[ $k ];
		}
	}

	return $a1;
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
function qtn_trim_string( $string, $chars = 200, $suffix = '...' ) {
	if ( strlen( $string ) > $chars ) {
		$string = mb_substr( $string, 0, ( $chars - mb_strlen( $suffix ) ) ) . $suffix;
	}

	return $string;
}

/**
 * Sanitize terms from an attribute text based.
 *
 * @param  string $term
 *
 * @return string
 */
function qtn_sanitize_term_text_based( $term ) {
	return trim( wp_unslash( strip_tags( $term ) ) );
}
