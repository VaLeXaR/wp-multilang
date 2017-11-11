<?php
/**
 * WPM Language functions
 *
 * Functions for getting params from WPM_Setup.
 *
 * @category      Core
 * @package       WPM/Functions
 * @version       1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPM\Includes\WPM_Setup;

/**
 * Get enabled languages
 *
 * @see WPM_Setup::get_languages()
 *
 * @return array
 */
function wpm_get_languages() {
	return WPM_Setup::instance()->get_languages();
}

/**
 * Get user language
 *
 * @see WPM_Setup::get_user_language()
 *
 * @return string
 */
function wpm_get_user_language() {
	return WPM_Setup::instance()->get_user_language();
}

/**
 * Get default locale
 *
 * @see WPM_Setup::get_default_locale()
 *
 * @return string
 */
function wpm_get_default_locale() {
	return WPM_Setup::instance()->get_default_locale();
}

/**
 * Get site language
 *
 * @since 2.0.0
 *
 * @see WPM_Setup::get_default_language()
 *
 * @return string
 */
function wpm_get_default_language() {
	return WPM_Setup::instance()->get_default_language();
}

/**
 * Get options
 *
 * @see WPM_Setup::get_options()
 *
 * @return array
 */
function wpm_get_lang_option() {
	return WPM_Setup::get_option( 'languages' );
}

/**
 * Get installed languages
 *
 * @see WPM_Setup::get_installed_languages()
 *
 * @return array
 */
function wpm_get_installed_languages() {
	return WPM_Setup::instance()->get_installed_languages();
}

/**
 * Get available translation
 *
 * @see WPM_Setup::get_translations()
 *
 * @return array
 */
function wpm_get_available_translations() {
	return WPM_Setup::instance()->get_translations();
}

/**
 * Get original home url
 *
 * @see WPM_Setup::get_original_home_url()
 *
 * @since 1.7.0
 *
 * @param bool $unslash
 *
 * @return string
 */
function wpm_get_orig_home_url( $unslash = true ) {
	return WPM_Setup::instance()->get_original_home_url( $unslash );
}

/**
 * Get original request uri
 *
 * @see WPM_Setup::get_original_request_uri()
 *
 * @since 1.7.0
 *
 * @return string
 */
function wpm_get_orig_request_uri() {
	return WPM_Setup::instance()->get_original_request_uri();
}

/**
 * Get site request uri
 *
 * @see WPM_Setup::get_site_request_uri()
 *
 * @since 2.0.1
 *
 * @return string
 */
function wpm_get_site_request_uri() {
	return WPM_Setup::instance()->get_site_request_uri();
}

/**
 * Get language for translation
 *
 * @since 1.7.0
 *
 * @return string
 */
function wpm_get_language() {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) ) {

		$languages = wpm_get_languages();
		$edit_lang = get_user_meta( get_current_user_id(), 'edit_lang', true );
		$query     = $_GET;

		if ( wp_doing_ajax() && ( $referrer = wp_get_raw_referer() ) ) {
			if ( strpos( $referrer, 'wp-admin/' ) !== false ) {
				$query = wp_parse_url( $referrer, PHP_URL_QUERY );
			} else {
				return wpm_get_user_language();
			}
		}

		$lang = ( isset( $query['edit_lang'] ) && isset( $languages [ wpm_clean( $query['edit_lang'] ) ] ) ) ? wpm_clean( $query['edit_lang'] ) : ( ( $edit_lang && isset( $languages[ $edit_lang ] ) ) ? $edit_lang : wpm_get_user_language() );
	} else {
		$lang = wpm_get_user_language();
	}

	return $lang;
}
