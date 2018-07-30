<?php
/**
 * WPM Language functions
 *
 * Functions for getting params from WPM_Setup.
 *
 * @category      Core
 * @package       WPM/Functions
 * @version       1.0.2
 * @author   Valentyn Riaboshtan
 */

use WPM\Includes\WPM_Setup;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get enabled languages
 *
 * @see WPM_Setup::get_languages()
 *
 * @return array
 */
function wpm_get_languages() {
	return wpm()->setup->get_languages();
}

/**
 * Get user language
 *
 * @see WPM_Setup::get_user_language()
 *
 * @return string
 */
function wpm_get_user_language() {
	return wpm()->setup->get_user_language();
}

/**
 * Get default locale
 *
 * @see WPM_Setup::get_default_locale()
 *
 * @return string
 */
function wpm_get_default_locale() {
	return wpm()->setup->get_default_locale();
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
	return wpm()->setup->get_default_language();
}

/**
 * Get options
 *
 * @see WPM_Setup::get_options()
 *
 * @return array
 */
function wpm_get_lang_option() {
	if ( version_compare( WPM_Setup::get_option( 'version' ), '2.0.0', '<' ) ) {
		return array();
	}

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
	return wpm()->setup->get_installed_languages();
}

/**
 * Get available translation
 *
 * @see WPM_Setup::get_translations()
 *
 * @return array
 */
function wpm_get_available_translations() {
	return wpm()->setup->get_translations();
}

/**
 * Get language for translation
 *
 * @since 1.7.0
 *
 * @return string
 */
function wpm_get_language() {

	$referrer = wp_get_raw_referer();

	if ( ( defined( 'REST_REQUEST' ) && ( 'GET' !== $_SERVER['REQUEST_METHOD'] || is_admin_url( $referrer ) ) ) || is_admin() ) {

		$languages = wpm_get_languages();
		$query     = $_GET;

		if ( wp_doing_ajax() ) {
			if ( $referrer && ! is_front_ajax() ) {
				$query = wp_parse_url( $referrer, PHP_URL_QUERY );
			} else {
				return wpm_get_user_language();
			}
		}

		if ( isset( $query['edit_lang'], $languages [ wpm_clean( $query['edit_lang'] ) ] ) ) {
			$lang = wpm_clean( $query['edit_lang'] );
		} else {
			$edit_lang = get_user_meta( get_current_user_id(), 'edit_lang', true );
			if ( $edit_lang && isset( $languages[ $edit_lang ] ) ) {
				$lang = $edit_lang;
			} else {
				$lang = wpm_get_user_language();
			}
		}

	} else {
		$lang = wpm_get_user_language();
	}

	return $lang;
}
