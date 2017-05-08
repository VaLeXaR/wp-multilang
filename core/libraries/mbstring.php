<?php
/**
 * File: mbstring.php
 * Implementation or stubs for PHP functions either missing from older PHP versions or not included by default.
 *
 * @package WPGlobus\Compat
 */

if ( ! function_exists( 'mb_strtolower' ) ) :
	/**
	 * @param string $str      String to convert.
	 * @param string $encoding Encoding parameter is not used.
	 *
	 * @return string
	 */
	function mb_strtolower(
		$str,
		// @formatter:off
		/** @noinspection PhpUnusedParameterInspection */ $encoding = null
		// @formatter:on
	) {
		return strtolower( $str );
	}
endif;

if ( ! function_exists( 'mb_stripos' ) ) :
	/**
	 * Finds position of first occurrence of a string within another, case insensitive
	 * @link http://php.net/manual/en/function.mb-stripos.php
	 *
	 * @param string $haystack The string from which to get the position of the first occurrence of needle
	 * @param string $needle   The string to find in haystack
	 * @param int    $offset   [optional] The position in haystack to start searching
	 * @param string $encoding [optional] Character encoding name to use. If it is omitted, internal character encoding is used.
	 *
	 * @return int Return the numeric position of the first occurrence of needle in the haystack string, or false if needle is not found.
	 */
	function mb_stripos(
		$haystack, $needle,
		// @formatter:off
		/** @noinspection PhpUnusedParameterInspection */ $offset = null,
		/** @noinspection PhpUnusedParameterInspection */ $encoding = null
		// @formatter:on
	) {
		return stripos( $haystack, $needle );
	}
endif;

/* EOF */
