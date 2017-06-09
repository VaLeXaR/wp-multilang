<?php
/**
 * WPM Admin Functions
 *
 * @author   VaLeXaR
 * @category Core
 * @package  WPM/Admin/Functions
 * @since    1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Translate response attachment
 * use get_post
 * @since 1.1.2
 *
 * @param $response
 *
 * @return mixed
 */
function wpm_translate_attachment_for_js( $response ) {
	$response['title']       = wpm_translate_string( $response['title'] );
	$response['caption']     = wpm_translate_string( $response['caption'] );
	$response['description'] = wpm_translate_string( $response['description'] );

	return $response;
}

add_filter( 'wp_prepare_attachment_for_js', 'wpm_translate_attachment_for_js' );
