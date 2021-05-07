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
	WPM_Setup::set_option( 'languages', $updated_languages );
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
	WPM_Setup::set_option( 'languages', $updated_languages );
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
		WPM_Setup::set_option( 'languages', $updated_languages );
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
		'wpm_show_untranslated_strings',
		'wpm_use_redirect',
		'wpm_use_prefix',
		'wpm_uninstall_translations',
	);

	foreach ( $options as $option ) {
		$value = get_option( $option );
		update_option( $option, 1 == $value ? 'yes' : 'no' );
	}
}

/**
 * Update DB Version.
 */
function wpm_update_211_db_version() {
	WPM_Install::update_db_version( '2.1.1' );
}

/**
 * Change syntax for term and post title.
 */
function wpm_update_214_change_syntax() {
	global $wpdb;

	//Replace '<!--:xx-->' and '{:xx}' by '[:xx]'.
	$adjust_syntax = function( $value ) {
		$value = preg_replace('#<!--(:[a-z-]*)-->#im', '[$1]', $value );
		$value = preg_replace('#{(:[a-z-]*)}#im', '[$1]', $value );
		return $value;
	};

	$results = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_title LIKE '%{:__}%' OR post_title LIKE '%<!--:__-->%';" );

	foreach ( $results as $result ) {
		$post_title = $adjust_syntax( $result->post_title );
		$wpdb->update( $wpdb->posts, array( 'post_title' => $post_title ), array( 'ID' => $result->ID ) );
	}

	$results = $wpdb->get_results( "SELECT term_id, `name` FROM {$wpdb->terms} WHERE `name` LIKE '%{:__}%' OR `name` LIKE '%<!--:__-->%';" );

	foreach ( $results as $result ) {
		$name = $adjust_syntax( $result->name );
		$wpdb->update( $wpdb->terms, array( 'name' => $name ), array( 'term_id' => $result->term_id ) );
	}
}

/**
 * Update DB Version.
 */
function wpm_update_214_db_version() {
	WPM_Install::update_db_version( '2.1.4' );
}
