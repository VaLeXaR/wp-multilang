<?php
/**
 * WPMPlugin Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend).
include( 'wpm-formatting-functions.php' );
include( 'wpm-language-functions.php' );
include( 'wpm-translation-functions.php' );
include( 'wpm-template-functions.php' );

/**
 * Load html files
 *
 * @param $path
 *
 * @return bool|string
 */
function wpm_get_template_html( $path ) {
	ob_start();

	$located = WPM()->template_path() . $path;
	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );

		return false;
	}

	include( $located );

	return ob_get_clean();
}

/**
 * Load assets
 *
 * @param $filename
 *
 * @return string
 */
function wpm_asset_path( $filename ) {
	$dist_path = str_replace( array( 'http:', 'https:' ), '', WPM()->plugin_url() ) . '/assets/';
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );
	static $manifest;

	if ( empty( $manifest ) ) {
		$manifest_path = WPM()->plugin_path() . '/assets/assets.json';
		$manifest      = new WPM\Core\Libraries\Json_Manifest( $manifest_path );
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
function wpm_enqueue_js( $code ) {
	global $wpm_queued_js;

	if ( empty( $wpm_queued_js ) ) {
		$wpm_queued_js = '';
	}

	$wpm_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function wpm_print_js() {
	global $wpm_queued_js;

	if ( ! empty( $wpm_queued_js ) ) {
		// Sanitize.
		$wpm_queued_js = wp_check_invalid_utf8( $wpm_queued_js );
		$wpm_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $wpm_queued_js );
		$wpm_queued_js = str_replace( "\r", '', $wpm_queued_js );

		$js = "<!-- WPM JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $wpm_queued_js });\n</script>\n";

		/**
		 * game_portal_queued_js filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo $js;

		unset( $wpm_queued_js );
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
function wpm_setcookie( $name, $value, $expire = 0, $secure = false ) {
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
function wpm_transaction_query( $type = 'start' ) {
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
function wpm_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

//add_filter( 'wpm_load_vendor_class_wpm_gutenberg', '__return_false' );
