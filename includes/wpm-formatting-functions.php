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
		} elseif ( is_numeric( $key ) ) {
			if ( ! in_array( $value, $merged, true ) ) {
				$merged[] = $value;
			}
		} else {
			$merged[ $key ] = $value;
		}
	}

	return $merged;
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
				if ( $result_item = wpm_array_diff_recursive( $val, $array2[ $key ] ) ) {
					$result[ $key ] = $result_item;
				}
			}
		} else {
			$result[ $key ] = $val;
		}
	}

	return $result;
}

/**
 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
 * to the end of the array.
 *
 * @param array $array
 * @param string $key
 * @param array $new
 *
 * @return array
 */
function wpm_array_insert_after( array $array, $key, array $new ) {
	$keys = array_keys( $array );
	$index = array_search( $key, $keys );
	$pos = false === $index ? count( $array ) : $index + 1;
	return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
}
