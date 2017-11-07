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
 * Update language flag
 */
function wpm_update_200_options() {

	$updated_languages = array();

	foreach ( wpm_get_languages() as $locale => $language ) {
		if ( isset( $language['slug'] ) ) {
			$slug = $language['slug'];
			$updated_languages[ $slug ] = array(
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
