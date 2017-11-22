<?php
/**
 * WP Multilang Updates
 *
 * Functions for updating data, used by updating.
 *
 * @category Core
 * @package  WPM/Functions
 */

use WPM\Includes\WPM_Install;
use WPM\Includes\WPM_Setup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update date and time format foe languages.
 */
function wpm_update_178_datetime_format() {
	$updated_languages = array();

	foreach ( wpm_get_languages() as $locale => $language ) {
		$language['date']             = $language['date'] ? $language['date'] : '';
		$language['time']             = $language['time'] ? $language['time'] : '';
		$updated_languages[ $locale ] = $language;
	}

	update_option( 'wpm_languages', $updated_languages );
}

/**
 * Update DB Version.
 */
function wpm_update_178_db_version() {
	WPM_Install::update_db_version( '1.7.8' );
}

/**
 * Update language flag
 */
function wpm_update_180_flags() {
	$updated_languages = array();

	foreach ( wpm_get_languages() as $locale => $language ) {
		$language['flag']             = $language['flag'] . '.png';
		$updated_languages[ $locale ] = $language;
	}

	update_option( 'wpm_languages', $updated_languages );
}

/**
 * Update DB Version.
 */
function wpm_update_180_db_version() {
	WPM_Install::update_db_version( '1.8.0' );
}

/**
 * Update language flag
 */
function wpm_update_200_options() {

	$updated_languages = array();

	foreach ( get_option( 'wpm_languages', array() ) as $locale => $language ) {
		if ( isset( $language['slug'] ) ) {
			$code = $language['slug'];
			$updated_languages[ $code ] = array(
				'enable'      => $language['enable'],
				'locale'      => $locale,
				'name'        => $language['name'],
				'translation' => $locale,
				'date'        => $language['date'],
				'time'        => $language['time'],
				'flag'        => $language['flag'],
			);
		}
	}

	if ( $updated_languages ) {
		update_option( 'wpm_languages', $updated_languages );
	}
}

/**
 * Update DB Version.
 */
function wpm_update_200_db_version() {
	WPM_Install::update_db_version( '2.0.0' );
}

/**
 * Delete configs from base. Move configs to cache.
 */
function wpm_update_210_delete_config() {
	delete_option( 'wpm_config' );
}

/**
 * Update DB Version.
 */
function wpm_update_210_db_version() {
	WPM_Install::update_db_version( '2.1.0' );
}

/**
 * Change options value.
 */
function wpm_update_211_change_options() {
	$options = array(
		'wpm_show_untranslated_strings' => 'yes',
		'wpm_use_redirect'              => 'no',
		'wpm_use_prefix'                => 'no',
		'wpm_uninstall_translations'    => 'no',
	);

	foreach ( $options as $option => $default ) {
		$value = get_option( $option, $default );
		update_option( $option, $value ? 'yes' : 'no' );
	}
}

/**
 * Update DB Version.
 */
function wpm_update_211_db_version() {
	WPM_Install::update_db_version( '2.1.1' );
}
