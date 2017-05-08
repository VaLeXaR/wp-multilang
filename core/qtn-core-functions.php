<?php
/**
 * qTranslateNext Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       GamePortal/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend).
include( 'libraries/mbstring.php' );
include( 'libraries/xml2array.php' );
include( 'qtn-formatting-functions.php' );
include( 'qtn-translation-functions.php' );
include( 'qtn-template-functions.php' );


function qtn_asset_path( $filename ) {
	$dist_path = str_replace( array( 'http:', 'https:' ), '', QN()->plugin_url() ) . '/dist/';
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );
	static $manifest;

	if ( empty( $manifest ) ) {
		$manifest_path = QN()->plugin_path() . '/dist/assets.json';
		$manifest      = new QtNext\Libraries\Json_Manifest( $manifest_path );
	}

	if ( array_key_exists( $file, $manifest->get() ) ) {
		return $dist_path . $directory . $manifest->get()[ $file ];
	} else {
		return $dist_path . $directory . $file;
	}
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code
 */

function qtn_enqueue_js( $code ) {
	global $qtn_queued_js;

	if ( empty( $qtn_queued_js ) ) {
		$qtn_queued_js = '';
	}

	$qtn_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function qtn_print_js() {
	global $qtn_queued_js;

	if ( ! empty( $qtn_queued_js ) ) {
		// Sanitize.
		$qtn_queued_js = wp_check_invalid_utf8( $qtn_queued_js );
		$qtn_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $qtn_queued_js );
		$qtn_queued_js = str_replace( "\r", '', $qtn_queued_js );

		$js = "<!-- GamePortal JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $qtn_queued_js });\n</script>\n";

		/**
		 * game_portal_queued_js filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo $js;

		unset( $qtn_queued_js );
	}
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @param  string  $name   Name of the cookie being set.
 * @param  string  $value  Value of the cookie.
 * @param  integer $expire Expiry of the cookie.
 * @param  string  $secure Whether the cookie should be served only over https.
 */
function qtn_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}

/**
 * Find all possible combinations of values from the input array and return in a logical order.
 *
 * @param array $input
 *
 * @return array
 */
function qtn_array_cartesian( $input ) {
	$input   = array_filter( $input );
	$results = array();
	$indexes = array();
	$index   = 0;

	// Generate indexes from keys and values so we have a logical sort order
	foreach ( $input as $key => $values ) {
		foreach ( $values as $value ) {
			$indexes[ $key ][ $value ] = $index ++;
		}
	}

	// Loop over the 2D array of indexes and generate all combinations
	foreach ( $indexes as $key => $values ) {
		// When result is empty, fill with the values of the first looped array
		if ( empty( $results ) ) {
			foreach ( $values as $value ) {
				$results[] = array( $key => $value );
			}

			// Second and subsequent input sub-array merging.
		} else {
			foreach ( $results as $result_key => $result ) {
				foreach ( $values as $value ) {
					// If the key is not set, we can set it
					if ( ! isset( $results[ $result_key ][ $key ] ) ) {
						$results[ $result_key ][ $key ] = $value;
						// If the key is set, we can add a new combination to the results array
					} else {
						$new_combination         = $results[ $result_key ];
						$new_combination[ $key ] = $value;
						$results[]               = $new_combination;
					}
				}
			}
		}
	}

	// Sort the indexes
	arsort( $results );

	// Convert indexes back to values
	foreach ( $results as $result_key => $result ) {
		$converted_values = array();

		// Sort the values
		arsort( $results[ $result_key ] );

		// Convert the values
		foreach ( $results[ $result_key ] as $key => $value ) {
			$converted_values[ $key ] = array_search( $value, $indexes[ $key ] );
		}

		$results[ $result_key ] = $converted_values;
	}

	return $results;
}

/**
 * Run a MySQL transaction query, if supported.
 *
 * @param string $type start (default), commit, rollback
 */
function qtn_transaction_query( $type = 'start' ) {
	global $wpdb;

	$wpdb->hide_errors();

	if ( ! defined( 'GP_USE_TRANSACTIONS' ) ) {
		define( 'GP_USE_TRANSACTIONS', true );
	}

	if ( GP_USE_TRANSACTIONS ) {
		switch ( $type ) {
			case 'commit' :
				$wpdb->query( 'COMMIT' );
				break;
			case 'rollback' :
				$wpdb->query( 'ROLLBACK' );
				break;
			default :
				$wpdb->query( 'START TRANSACTION' );
				break;
		}
	}
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 */
function qtn_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}
