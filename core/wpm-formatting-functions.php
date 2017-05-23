<?php
/**
 * WPM Formatting
 *
 * Functions for formatting data.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
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
function wpm_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpm_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Merge two arrays.
 *
 * @param array
 * @param array
 *
 * @return array
 */
function wpm_array_merge_recursive( array & $array1, array & $array2 ) {
	$merged = $array1;

	foreach ( $array2 as $key => & $value ) {
		if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
			$merged[ $key ] = wpm_array_merge_recursive( $merged[ $key ], $value );
		} else if ( is_numeric( $key ) ) {
			if ( ! in_array( $value, $merged ) ) {
				$merged[] = $value;
			}
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
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
function wpm_trim_string( $string, $chars = 200, $suffix = '...' ) {
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
function wpm_sanitize_term_text_based( $term ) {
	return trim( wp_unslash( strip_tags( $term ) ) );
}

/**
 * Diff multidimensional array
 *
 * @param $array1
 * @param $array2
 *
 * @return array
 */
function wpm_array_diff_recursive( $array1, $array2 ) {
	$result = array();
	foreach ( $array1 as $key => $val ) {
		if ( isset( $array2[ $key ] ) ) {
			if ( is_array( $val ) && $array2[ $key ] ) {
				$result[ $key ] = wpm_array_diff_recursive( $val, $array2[ $key ] );
			}
		} else {
			$result[ $key ] = $val;
		}
	}

	return $result;
}
