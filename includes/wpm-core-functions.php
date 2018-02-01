<?php
/**
 * WPMPlugin Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   Valentyn Riaboshtan
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
include( 'wpm-config-functions.php' );
include( 'wpm-template-functions.php' );

/**
 * Load html files
 *
 * @param $slug
 * @param string $name
 * @param string $custom_dir
 * @param array $args
 *
 * @return bool|string
 * @internal param $path
 *
 */
function wpm_get_template( $slug, $name = '', $custom_dir = '', $args = array() ) {
	$template = '';

	// The plugin path
	$template_path = wpm()->template_path();

	// Look in yourtheme/slug-name.php and yourtheme/wp-multilang/slug-name.php
	if ( $name ) {
		$template = locate_template( array( "{$slug}-{$name}.php", "wp-multilang/{$slug}-{$name}.php" ) );
	}

	// If a custom path was defined, check that next
	if ( ! $template && $custom_dir && file_exists( trailingslashit( $custom_dir ) . "{$slug}-{$name}.php" ) ) {
		$template = trailingslashit( $custom_dir ) . "{$slug}-{$name}.php";
	}

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( $template_path . "{$slug}-{$name}.php" ) ) {
		$template = $template_path . "{$slug}-{$name}.php";
	}

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wp-multilang/slug.php
	if ( ! $template ) {
		$template = locate_template( array( "{$slug}.php", "wp-multilang/{$slug}.php" ) );
	}

	// If a custom path was defined, check that next
	if ( ! $template && $custom_dir && file_exists( trailingslashit( $custom_dir ) . "{$slug}.php" ) ) {
		$template = trailingslashit( $custom_dir ) . "{$slug}.php";
	}

	// Get default slug-name.php
	if ( ! $template && file_exists( $template_path . "{$slug}.php" ) ) {
		$template = $template_path . "{$slug}.php";
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template = apply_filters( 'wpm_get_template_part', $template, $slug, $name );

	// Load template if we've found one
	if ( $template ) {

		// Extract args if there are any
		if ( is_array( $args ) && count( $args ) > 0 ) {
			extract( $args );
		}

		do_action( 'wpm_before_template_part', $template, $slug, $name, $custom_dir, $args );

		ob_start();

		include( $template );

		do_action( 'wpm_after_template_part', $template, $slug, $name, $custom_dir, $args );

		return ob_get_clean();
	}
}

/**
 * Load assets
 *
 * @param $filename
 *
 * @return string
 */
function wpm_asset_path( $filename ) {
	$dist_path = str_replace( array( 'http:', 'https:' ), '', wpm()->plugin_url() ) . '/assets/';
	$directory = dirname( $filename ) . '/';
	$file      = basename( $filename );

	return $dist_path . $directory . $file;
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
 * @param  bool  $secure Whether the cookie should be served only over https.
 */
function wpm_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie( $name, $value, $expire,  COOKIEPATH ? COOKIEPATH : '/', null, $secure );
		if ( COOKIEPATH != SITECOOKIEPATH ) {
			setcookie( $name, $value, $expire, SITECOOKIEPATH, null, $secure );
		}
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
	}
}


/**
 * Get current url from $_SERVER
 *
 * @return string
 */
function wpm_get_current_url() {
	$url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	return $url;
}


/**
 * Get current url from $_SERVER and translate it
 *
 * @param string $lang
 *
 * @return string
 */
function wpm_translate_current_url( $lang = '' ) {
	$url = wpm_get_current_url();

	if ( ! $lang ) {
		$lang = wpm_get_language();
	}

	$url = wpm_translate_url( $url, $lang );

	return apply_filters( 'wpm_get_current_url', $url, $lang );
}

/**
 * Get original home url
 *
 * @see WPM_Setup::get_original_home_url()
 *
 * @since 1.7.0
 *
 * @return string
 */
function wpm_get_orig_home_url() {
	$home_url = wpm()->setup->get_original_home_url();

	return apply_filters( 'wpm_get_original_home_url', $home_url );
}


/**
 * Translate escaping text
 *
 * @param string $string
 *
 * @return string
 */
function wpm_escaping_text( $string ) {
	if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {
		$string = wpm_translate_string( $string );
	}

	return $string;
}

add_filter( 'attribute_escape', 'wpm_escaping_text', 5 );
add_filter( 'esc_textarea', 'wpm_escaping_text', 5 );
add_filter( 'esc_html', 'wpm_escaping_text', 5 );
add_filter( 'localization', 'wpm_translate_string', 5 );
add_filter( 'gettext', 'wpm_translate_string', 5 );

if ( ! function_exists( 'remove_class_filter' ) ) {
	/**
	 * Remove Class Filter Without Access to Class Object
	 *
	 * In order to use the core WordPress remove_filter() on a filter added with the callback
	 * to a class, you either have to have access to that class object, or it has to be a call
	 * to a static method.  This method allows you to remove filters with a callback to a class
	 * you don't have access to.
	 *
	 * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
	 * Updated 2-27-2017 to use internal WordPress removal for 4.7+ (to prevent PHP warnings output)
	 *
	 * @param string $tag         Filter to remove
	 * @param string $class_name  Class name for the filter's callback
	 * @param string $method_name Method name for the filter's callback
	 * @param int    $priority    Priority of the filter (default 10)
	 *
	 * @return bool Whether the function is removed.
	 */
	function remove_class_filter( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		global $wp_filter;
		// Check that filter actually exists first
		if ( ! isset( $wp_filter[ $tag ] ) ) {
			return false;
		}
		/**
		 * If filter config is an object, means we're using WordPress 4.7+ and the config is no longer
		 * a simple array, rather it is an object that implements the ArrayAccess interface.
		 *
		 * To be backwards compatible, we set $callbacks equal to the correct array as a reference (so $wp_filter is updated)
		 *
		 * @see https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/
		 */
		if ( is_object( $wp_filter[ $tag ] ) && isset( $wp_filter[ $tag ]->callbacks ) ) {
			// Create $fob object from filter tag, to use below
			$fob       = $wp_filter[ $tag ];
			$callbacks = &$wp_filter[ $tag ]->callbacks;
		} else {
			$callbacks = &$wp_filter[ $tag ];
		}
		// Exit if there aren't any callbacks for specified priority
		if ( ! isset( $callbacks[ $priority ] ) || empty( $callbacks[ $priority ] ) ) {
			return false;
		}
		// Loop through each filter for the specified priority, looking for our class & method
		foreach ( (array) $callbacks[ $priority ] as $filter_id => $filter ) {
			// Filter should always be an array - array( $this, 'method' ), if not goto next
			if ( ! isset( $filter['function'] ) || ! is_array( $filter['function'] ) ) {
				continue;
			}
			// If first value in array is not an object, it can't be a class
			if ( ! is_object( $filter['function'][0] ) ) {
				continue;
			}
			// Method doesn't match the one we're looking for, goto next
			if ( $filter['function'][1] !== $method_name ) {
				continue;
			}
			// Method matched, now let's check the Class
			if ( get_class( $filter['function'][0] ) === $class_name ) {
				// WordPress 4.7+ use core remove_filter() since we found the class object
				if ( isset( $fob ) ) {
					// Handles removing filter, reseting callback priority keys mid-iteration, etc.
					$fob->remove_filter( $tag, $filter['function'], $priority );
				} else {
					// Use legacy removal process (pre 4.7)
					unset( $callbacks[ $priority ][ $filter_id ] );
					// and if it was the only filter in that priority, unset that priority
					if ( empty( $callbacks[ $priority ] ) ) {
						unset( $callbacks[ $priority ] );
					}
					// and if the only filter for that tag, set the tag to an empty array
					if ( empty( $callbacks ) ) {
						$callbacks = array();
					}
					// Remove this filter from merged_filters, which specifies if filters have been sorted
					unset( $GLOBALS['merged_filters'][ $tag ] );
				}
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'remove_class_action' ) ) {
	/**
	 * Remove Class Action Without Access to Class Object
	 *
	 * In order to use the core WordPress remove_action() on an action added with the callback
	 * to a class, you either have to have access to that class object, or it has to be a call
	 * to a static method.  This method allows you to remove actions with a callback to a class
	 * you don't have access to.
	 *
	 * Works with WordPress 1.2+ (4.7+ support added 9-19-2016)
	 *
	 * @param string $tag         Action to remove
	 * @param string $class_name  Class name for the action's callback
	 * @param string $method_name Method name for the action's callback
	 * @param int    $priority    Priority of the action (default 10)
	 *
	 * @return bool               Whether the function is removed.
	 */
	function remove_class_action( $tag, $class_name = '', $method_name = '', $priority = 10 ) {
		return remove_class_filter( $tag, $class_name, $method_name, $priority );
	}
}

/**
 * Define a constant if it is not already defined.
 *
 * @since  1.8.0
 * @param string $name  Constant name.
 * @param string $value Value.
 */
function wpm_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Get an item of post data if set, otherwise return a default value.
 *
 * @since  1.8.0
 * @param  string $key
 * @param  string $default
 * @return mixed value sanitized by wpm_clean
 */
function wpm_get_post_data_by_key( $key, $default = '' ) {
	return wpm_clean( wpm_get_var( $_POST[ $key ], $default ) );
}

/**
 * Get data if set, otherwise return a default value or null. Prevents notices when data is not set.
 *
 * @since  1.8.0
 * @param  mixed $var
 * @param  string $default
 * @return mixed value sanitized by wpm_clean
 */
function wpm_get_var( &$var, $default = null ) {
	return isset( $var ) ? $var : $default;
}

/**
 * Delete expired transients.
 *
 * Deletes all expired transients. The multi-table delete syntax is used.
 * to delete the transient record from table a, and the corresponding.
 * transient_timeout record from table b.
 *
 * Based on code inside core's upgrade_network() function.
 *
 * @since  1.8.0
 *
 * @return int Number of transients that were cleared.
 */
function wpm_delete_expired_transients() {
	global $wpdb;

	$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
		AND b.option_value < %d";
	$rows = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

	$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE %s
		AND a.option_name NOT LIKE %s
		AND b.option_name = CONCAT( '_site_transient_timeout_', SUBSTRING( a.option_name, 17 ) )
		AND b.option_value < %d";
	$rows2 = $wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_site_transient_' ) . '%', $wpdb->esc_like( '_site_transient_timeout_' ) . '%', time() ) );

	return absint( $rows + $rows2 );
}
add_action( 'wpm_installed', 'wpm_delete_expired_transients' );

/**
 * Display a WP Multilang help tip.
 *
 * @since  2.1.1
 *
 * @param  string $tip        Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 */
function wpm_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = wpm_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="wpm-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Get post by title
 *
 * @param $page_title
 * @param string $output
 * @param string $post_type
 *
 * @return array|null|WP_Post
 */
function wpm_get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;

	$like = '%' . $wpdb->esc_like( esc_sql( '[:' . wpm_get_language() . ']' . $page_title . '[:' ) ) . '%';

	if ( is_array( $post_type ) ) {
		$post_type = esc_sql( $post_type );
		$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
		$sql = $wpdb->prepare( "
			SELECT ID, post_title
			FROM $wpdb->posts
			WHERE post_title LIKE %s
			AND post_type IN ($post_type_in_string)
		", $like );
	} else {
		$sql = $wpdb->prepare( "
			SELECT ID, post_title
			FROM $wpdb->posts
			WHERE post_title LIKE %s
			AND post_type = %s
		", $like, $post_type );
	}

	$page = $wpdb->get_var( $sql );

	if ( $page ) {
		return get_post( $page, $output );
	}
}

if ( ! function_exists( 'is_front_ajax' ) ) {
	/**
	 * Check if it is ajax from front
	 *
	 * @return bool
	 */
	function is_front_ajax() {
		if ( wp_doing_ajax() && ( $referrer = wp_get_raw_referer() ) ) {
			if ( strpos( $referrer, 'wp-admin/' ) === false ) {
				return true;
			}
		}

		return false;
	}
}
