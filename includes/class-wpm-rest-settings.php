<?php
/**
 * WPM REST Settings Class
 *
 * @package  WPM/Includes
 */

namespace WPM\Includes;

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
		add_filter( 'rest_pre_update_setting', array( $this, 'update_site_language_setting' ), 10, 3 );
	}

	/**
	 * Add settings to REST API
	 */
	public function register_initial_settings() {
		register_setting( 'wpm-settings', 'wpm_site_language', array(
			'description'  => __( 'Site Language', 'wp-multilang' ),
			'type'         => 'string',
			'default'      => false,
			'show_in_rest' => array(
				'name' => 'site_language',
			),
		) );

		$language_options = apply_filters( 'wpm_rest_schema_languages', array(
			'enable' => array(
				'type' => 'boolean',
			),
			'code' => array(
				'type' => 'string',
			),
			'locale' => array(
				'type' => 'string',
			),
			'name' => array(
				'type' => 'string',
			),
			'translation' => array(
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
		));

		register_setting( 'wpm-settings', 'wpm_languages', array(
			'description'  => __( 'Multilingual Settings', 'wp-multilang' ),
			'default'      => array(),
			'show_in_rest' => array(
				'name'   => 'multilingual_settings',
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type' => 'object',
						'properties' => $language_options,
					),
				),
			),
		) );
	}

	/**
	 * Ges site languages
	 *
	 * @since 1.8.0
	 *
	 * @param $value
	 * @param $name
	 * @param $args
	 *
	 * @return array
	 */
	public function get_languages_setting( $value, $name, $args ) {

		if ( 'multilingual_settings' !== $name ) {
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

		if ( 'multilingual_settings' !== $name || ! current_user_can( 'manage_options' ) ) {
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

				if ( empty( $item['code'] ) || empty( $item['locale'] ) ) {
					$error = true;
					break;
				}

				$code = wpm_sanitize_lang_slug( $item['code'] );

				if ( ! $code ) {
					break;
				}

				$languages[ $code ] = array(
					'enable'      => $item['enable'] ? 1 : 0,
					'locale'      => $item['locale'],
					'name'        => $item['name'],
					'translation' => $item['translation'] ?: 'en_US',
					'date'        => $item['date'],
					'time'        => $item['time'],
					'flag'        => $item['flag'],
				);

				if ( isset( $translations[ $item['translation'] ] ) && wp_can_install_language_pack() && ( ! is_multisite() || is_super_admin() ) ) {
					wp_download_language_pack( $item['translation'] );
				}
			}


			if ( ! $error ) {
				$languages = apply_filters( 'wpm_save_languages', $languages, $request );
				$locale    = $languages[ wpm_get_default_language() ]['translation'];
				update_option( 'WPLANG', 'en_US' !== $locale ? $locale : '' );
				update_option( 'wpm_languages', $languages );
			}
		}// End if().

		return true;
	}


	/**
	 * Save site language
	 *
	 * @since 2.0.0
	 *
	 * @param $updated
	 * @param $name
	 * @param $request
	 *
	 * @return mixed
	 */
	public function update_site_language_setting( $updated, $name, $request ) {

		if ( 'site_language' !== $name || ! current_user_can( 'manage_options' ) ) {
			return $updated;
		}

		$request   = wpm_clean( $request );
		$languages = wpm_get_languages();
		$locale    = $languages[ $request ]['translation'];

		if ( $languages ) {
			update_option( 'WPLANG', 'en_US' !== $locale ? $locale : '' );
		}

		return $updated;
	}
}
