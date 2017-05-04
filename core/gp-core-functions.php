<?php
/**
 * GamePortal Core Functions
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
include( 'gp-conditional-functions.php' );
include( 'gp-user-functions.php' );
include( 'gp-formatting-functions.php' );
include( 'gp-page-functions.php' );
include( 'gp-rest-functions.php' );
include( 'gp-level-functions.php' );
include( 'gp-game-functions.php' );
include( 'gp-purchase-functions.php' );
include( 'gp-account-functions.php' );

/**
 * Get template part (for templates like the shop-loop).
 *
 * @access public
 *
 * @param mixed  $slug
 * @param string $name (default: '')
 */
function gp_get_template_part( $slug, $name = '' ) {
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/game-portal/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", GP()->template_path() . "{$slug}-{$name}.php" ) );
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( GP()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
		$template = GP()->plugin_path() . "/templates/{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/game-portal/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", GP()->template_path() . "{$slug}.php" ) );
	}

	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @access public
 *
 * @param string $template_name
 * @param array  $args          (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 */
function gp_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = gp_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );

		return;
	}

	include( $located );
}

/**
 * Like gp_get_template, but returns the HTML instead of outputting.
 * @see gp_get_template
 *
 * @param string $template_name
 */
function gp_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	ob_start();
	gp_get_template( $template_name, $args, $template_path, $default_path );

	return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @access public
 *
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path  (default: '')
 *
 * @return string
 */
function gp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = GP()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = GP()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template/
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return $template;
}

/**
 * Send HTML emails from GamePortal.
 *
 * @param mixed  $to
 * @param mixed  $subject
 * @param mixed  $message
 * @param string $headers     (default: "Content-Type: text/html\r\n")
 * @param string $attachments (default: "")
 */
function gp_mail( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "" ) {
	$mailer = GP()->mailer();

	$mailer->send( $to, $subject, $message, $headers, $attachments );
}

/**
 * Get an image size.
 *
 * @param mixed $image_size
 *
 * @return array
 */
function gp_get_image_size( $image_size ) {
	if ( is_array( $image_size ) ) {
		$width  = isset( $image_size[0] ) ? $image_size[0] : '300';
		$height = isset( $image_size[1] ) ? $image_size[1] : '300';
		$crop   = isset( $image_size[2] ) ? $image_size[2] : 1;

		$size = array(
			'width'  => $width,
			'height' => $height,
			'crop'   => $crop
		);

	} else {
		$size = array(
			'width'  => '300',
			'height' => '300',
			'crop'   => 1
		);
	}

	return $size;
}


function gp_asset_path( $filename ) {
	$dist_path = str_replace( array( 'http:', 'https:' ), '', GP()->plugin_url() ) . '/dist/';
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );
	static $manifest;

	if ( empty( $manifest ) ) {
		$manifest_path = GP()->plugin_path() . '/dist/assets.json';
		$manifest      = new GP\Libraries\Json_Manifest( $manifest_path );
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

function gp_enqueue_js( $code ) {
	global $gp_queued_js;

	if ( empty( $wc_queued_js ) ) {
		$gp_queued_js = '';
	}

	$gp_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function gp_print_js() {
	global $gp_queued_js;

	if ( ! empty( $gp_queued_js ) ) {
		// Sanitize.
		$gp_queued_js = wp_check_invalid_utf8( $gp_queued_js );
		$gp_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $gp_queued_js );
		$gp_queued_js = str_replace( "\r", '', $gp_queued_js );

		$js = "<!-- GamePortal JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $gp_queued_js });\n</script>\n";

		/**
		 * game_portal_queued_js filter.
		 *
		 * @param string $js JavaScript code.
		 */
		echo $js;

		unset( $gp_queued_js );
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
function gp_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure );
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}

// This function can be removed when WP 3.9.2 or greater is required.
if ( ! function_exists( 'hash_equals' ) ) :
	/**
	 * Compare two strings in constant time.
	 *
	 * This function was added in PHP 5.6.
	 * It can leak the length of a string.
	 *
	 * @param string $a Expected string.
	 * @param string $b Actual string.
	 *
	 * @return bool Whether strings are equal.
	 */
	function hash_equals( $a, $b ) {
		$a_length = strlen( $a );
		if ( $a_length !== strlen( $b ) ) {
			return false;
		}
		$result = 0;

		// Do not attempt to "optimize" this.
		for ( $i = 0; $i < $a_length; $i ++ ) {
			$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
		}

		return $result === 0;
	}
endif;

/**
 * Generate a rand hash.
 *
 * @since  2.4.0
 * @return string
 */
function gp_rand_hash() {
	if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
		return bin2hex( openssl_random_pseudo_bytes( 20 ) );
	} else {
		return sha1( wp_rand() );
	}
}

/**
 * WC API - Hash.
 *
 * @param  string $data
 *
 * @return string
 */
function gp_api_hash( $data ) {
	return hash_hmac( 'sha256', $data, 'gp-api' );
}

/**
 * Find all possible combinations of values from the input array and return in a logical order.
 *
 * @param array $input
 *
 * @return array
 */
function gp_array_cartesian( $input ) {
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
function gp_transaction_query( $type = 'start' ) {
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
 * Outputs a "back" link so admin screens can easily jump back a page.
 *
 * @param string $label Title of the page to return to.
 * @param string $url   URL of the page to return to.
 */
function gp_back_link( $label, $url ) {
	echo '<small class="gp-admin-breadcrumb"><a href="' . esc_url( $url ) . '" title="' . esc_attr( $label ) . '">&#x2934;</a></small>';
}

/**
 * Display a GamePortal help tip.
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 *
 * @return string
 */
function gp_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = gp_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="game-portal-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 */
function gp_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}


/**
 * Check if coupons are enabled.
 * Filterable.
 *
 * @return array
 */
function gp_coupons_enabled() {
	return get_option( 'wpspsc_enable_coupon' );
}


function gp_multiple_upload_files( $files_field ) {

	$files_ids = [];

	if ( isset( $_FILES ) && $_FILES[ $files_field ] ) {

		$files = $_FILES[ $files_field ];

		foreach ( $files['name'] as $key => $value ) {

			if ( $files['name'][ $key ] ) {

				$file = array(
					'name'     => $files['name'][ $key ],
					'type'     => $files['type'][ $key ],
					'tmp_name' => $files['tmp_name'][ $key ],
					'error'    => $files['error'][ $key ],
					'size'     => $files['size'][ $key ]
				);

				$files_ids[] = gp_upload_file( $file );
			}
		}
	}

	return $files_ids;
}


function gp_upload_file( $files_field ) {

	if ( $_FILES[ $files_field ]['error'] !== UPLOAD_ERR_OK ) {
		__return_false();
	}

	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	$file_id = media_handle_sideload( $files_field, 0 );

	return $file_id;
}


function gp_delete_post( $post_id ) {

	if ( ! get_post( $post_id ) ) {
		return new WP_Error( 'post-delete-error-invalid-post-id', __( 'Invalid post ID', 'game-portal' ) );
	}

	if ( get_current_user_id() != get_post_field( 'post_author', $post_id ) ) {
		return new WP_Error( 'post-delete-error-invalid-permission', __( 'You have not permission for deleting this item', 'game-portal' ) );
	}

	$result = wp_delete_post( $post_id, true );

	if ( ! $result ) {
		return new WP_Error( 'post-delete-error-invalid-permission', __( 'Deleting error', 'game-portal' ) );
	} else {
		return true;
	}
}
