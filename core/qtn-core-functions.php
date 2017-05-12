<?php
/**
 * qTranslateNext Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       qTranslateNext/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend).
include( 'libraries/mbstring.php' );
include( 'libraries/xml2array.php' );
include( 'qtn-language-functions.php' );
include( 'qtn-formatting-functions.php' );
include( 'qtn-translation-functions.php' );
include( 'qtn-template-functions.php' );


function gp_get_template_html( $path ) {
	ob_start();

	$located = QN()->template_path() . $path;
	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );

		return false;
	}

	include( $located );

	return ob_get_clean();
}


function qtn_asset_path( $filename ) {
	$dist_path = str_replace( array( 'http:', 'https:' ), '', QN()->plugin_url() ) . '/assets/';
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );
	static $manifest;

	if ( empty( $manifest ) ) {
		$manifest_path = QN()->plugin_path() . '/assets/assets.json';
		$manifest      = new QtNext\Core\Libraries\Json_Manifest( $manifest_path );
	}

	if ( array_key_exists( $file, $manifest->get() ) ) {
		return $dist_path . $directory . $manifest->get()[ $file ];
	} else {
		return $dist_path . $directory . $file;
	}
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

		$js = "<!-- qTranslateNext JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $qtn_queued_js });\n</script>\n";

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
