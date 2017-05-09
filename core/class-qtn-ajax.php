<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * qTranslateNext QtN_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    QtN_AJAX
 * @version  1.0.0
 * @package  qTranslateNext/Classes
 * @category Class
 * @author   VaLeXaR
 */
class QtN_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_qtn_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get GP Ajax Endpoint.
	 *
	 * @param  string $request Optional
	 *
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'qtn-ajax', $request ) );
	}

	/**
	 * Set WC AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['qtn-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'QTN_DOING_AJAX' ) ) {
				define( 'QTN_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for GP Ajax Requests
	 */
	private static function qtn_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for GP Ajax request and fire action.
	 */
	public static function do_qtn_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['qtn-ajax'] ) ) {
			$wp_query->set( 'qtn-ajax', sanitize_text_field( $_GET['qtn-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'qtn-ajax' ) ) {
			self::qtn_ajax_headers();
			do_action( 'qtn_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'level_ordering'        => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_qtranslate_next_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_qtranslate_next_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GP AJAX can be used for frontend ajax requests
				add_action( 'qtn_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}
}
