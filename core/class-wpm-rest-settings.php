<?php
/**
 * WPM REST Settings Class
 *
 * @package  WPM/Core
 */

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_REST_Settings Class.
 */
class WPM_REST_Settings {

	/**
	 * WPM_REST_Settings constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_initial_settings' ) );
		add_filter( 'rest_pre_get_setting', array( $this, 'get_languages_setting' ), 10, 3 );
		add_filter( 'rest_pre_update_setting', array( $this, 'update_languages_setting' ), 10, 3 );
	}

	/**
	 * Add settings to REST API
	 */
	public function register_initial_settings() {
		register_setting( 'general', 'wpm_languages', array(
			'description'  => __( 'Multilingual Settings', 'wpm' ),
			'default'      => array(),
			'show_in_rest' => array(
				'name'   => 'multilingual_settings',
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type' => 'object',
						'properties' => array(
							'enable' => array(
								'type' => 'boolean',
							),
							'slug' => array(
								'type' => 'string',
							),
							'name' => array(
								'type' => 'string',
							),
							'date' => array(
								'type' => 'string',
							),
							'time' => array(
								'type' => 'string',
							),
							'flag' => array(
								'type' => 'string',
							),
							'locale' => array(
								'type' => 'string',
							),
						),
					),
				),
			),
		) );
		register_setting( 'general', 'wpm_show_untranslated_strings', array(
			'description'  => __( 'Show untranslated strings', 'wpm' ),
			'type'         => 'boolean',
			'default'      => false,
			'show_in_rest' => array(
				'name' => 'show_untranslated_strings',
			),
		) );
	}


	public function get_languages_setting( $value, $name, $args ) {

		if ( 'multilingual_settings' != $name ) {
			return $value;
		}

		$languages        = get_option( $args['option_name'], $args['schema']['default'] );
		$format_languages = array();

		foreach ( $languages as $locale => $language ) {
			$language['locale'] = $locale;
			$format_languages[] = $language;
		}

		return $format_languages;
	}

	/**
	 * Save WPM languages
	 *
	 * @param $updated
	 * @param $name
	 * @param $request
	 *
	 * @return bool
	 *
	 */
	public function update_languages_setting( $updated, $name, $request ) {

		if ( 'multilingual_settings' != $name ) {
			return $updated;
		}

		$request   = wpm_clean( $request );
		$languages = array();

		if ( $request ) {
			$error               = false;
			$translations        = wpm_get_available_translations();
			$installed_languages = wpm_get_installed_languages();

			foreach ( $installed_languages as $installed_language ) {
				if ( isset( $translations[ $installed_language ] ) ) {
					unset( $translations[ $installed_language ] );
				}
			}

			foreach ( $request as $item ) {

				if ( empty( $item['slug'] ) || empty( $item['locale'] ) ) {
					$error = true;
					break;
				}

				$locale = $item['locale'];

				$languages[ $locale ] = array(
					'enable' => $item['enable'] ? 1 : 0,
					'slug'   => sanitize_title( $item['slug'] ),
					'name'   => $item['name'],
					'date'   => $item['date'],
					'time'   => $item['time'],
					'flag'   => $item['flag'],
				);

				if ( isset( $translations[ $locale ] ) && wp_can_install_language_pack() && ( ! is_multisite() || is_super_admin() ) ) {
					wp_download_language_pack( $locale );
				}
			}

			if ( ! $error ) {
				update_option( 'wpm_languages', $languages );
			}
		}// End if().

		return true;
	}
}
