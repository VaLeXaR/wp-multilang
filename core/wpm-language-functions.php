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

use WPM\Core\WPM_Setup;

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
 * Get enabled languages
 *
 * @see WPM_Setup::get_languages()
 *
 * @return array
 */
function wpm_get_all_languages() {
	$options   = wpm_get_options();
	$languages = array();

	foreach ( $options as $locale => $language ) {
		$languages[ $locale ] = $language['slug'];
	}

	return $languages;
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
 * Get config
 *
 * @see WPM_Setup::get_config()
 *
 * @return array
 */
function wpm_get_config() {
	return WPM_Setup::instance()->get_config();
}

/**
 * Get options
 *
 * @see WPM_Setup::get_options()
 *
 * @return array
 */
function wpm_get_options() {
	return WPM_Setup::instance()->get_options();
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
 * @return string
 */
function wpm_get_orig_home_url() {
	return WPM_Setup::instance()->get_original_home_url();
}

/**
 * Get available translation
 *
 * @see WPM_Setup::get_translations()
 *
 * @since 1.7.0
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

		if ( wp_doing_ajax() && ( $referrer = wp_get_raw_referer() ) ) {

			if ( strpos( $referrer, admin_url() ) === false ) {
				return wpm_get_user_language();
			}
		}

		$edit_lang = get_user_meta( get_current_user_id(), 'edit_lang', true );

		$lang = ( isset( $_GET['edit_lang'] ) && in_array( wpm_clean( $_GET['edit_lang'] ), $languages, true ) ) ? wpm_clean( $_GET['edit_lang'] ) : ( ( $edit_lang && in_array( $edit_lang, $languages, true ) ) ? $edit_lang : wpm_get_user_language() );
	} else {
		$lang = wpm_get_user_language();
	}

	return $lang;
}
