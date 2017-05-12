<?php
/**
 *
 * @class   QtN_Config
 */

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QtN_Config {

	/**
	 * Order factory instance.
	 *
	 * @var string
	 */
	public $default_locale = '';

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $languages = array();

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $installed_languages = array();

	/**
	 * Order factory instance.
	 *
	 * @var string
	 */
	public $user_language = '';

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $translations = array();

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $settings = array();


	public function get_options() {
		if ( ! $this->options ) {
			$this->options = get_option( 'qtn_languages' );
		}

		return $this->options;
	}


	public function get_installed_languages() {
		if ( ! $this->installed_languages ) {
			$this->installed_languages = array_merge( array( 'en_US' ), get_available_languages() );
		}

		return $this->installed_languages;
	}


	public function get_languages() {
		if ( ! $this->languages ) {

			$options = $this->get_options();

			foreach ( $options as $locale => $language ) {
				if ( $language['enable'] ) {
					$this->languages[ $locale ] = $language['slug'];
				}
			}
		}

		return $this->languages;
	}


	public function get_default_locale() {
		if ( ! $this->default_locale ) {
			$this->default_locale = get_option( 'WPLANG' ) ?  get_option( 'WPLANG' ) : 'en_US';
		}

		return $this->default_locale;
	}


	public function get_user_language() {
		if ( ! $this->user_language ) {
			$this->set_user_language();
		}

		return $this->user_language;
	}


	public function set_user_language() {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$default_locale = $this->get_default_locale();
			if ( isset( $_GET['lang'] ) ) {
				$languages = $this->get_languages();
				$lang = qtn_clean( $_GET['lang'] );
				if ( ! in_array( $lang, $languages ) ) {
					$lang           = $languages[ $default_locale ];
				}
				qtn_setcookie( 'language', $lang, time() + MONTH_IN_SECONDS );
			}

			if ( ! isset( $_COOKIE['language'] ) ) {
				qtn_setcookie( 'language', $default_locale, time() + MONTH_IN_SECONDS );
			} else {
				$this->user_language = qtn_clean( $_COOKIE['language'] );
			}

		} else {
			$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

			if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
				$this->user_language = $match[1];
			}
		}

		if ( isset( $_GET['lang'] ) ) {
			$this->user_language = qtn_clean( $_GET['lang'] );
		}
	}


	public function set_locale() {
		global $locale;

		require_once( ABSPATH . 'wp-includes/pluggable.php' );

		$language       = $this->get_languages();
		$default_locale = $this->get_default_locale();

		foreach ( $language as $key => $value ) {

			$user_language = $this->get_user_language();

			if ( ( $value == $user_language ) ) {
				$locale = $key;
				if ( $locale == $default_locale && ! is_admin() && ! isset( $_GET['lang'] ) ) {
					wp_redirect( home_url( str_replace( '/' . $user_language . '/', '/', $_SERVER['REQUEST_URI'] ) ), 301 );
					exit;
				}
				break;
			}
		}

		if ( ! $this->user_language ) {
			$this->user_language = $this->languages[ $default_locale ];
		}
	}


	public function get_translations() {

		if ( ! $this->translations ) {
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$available_translations          = wp_get_available_translations();
			$available_translations['en_US'] = array(
				'native_name' => 'English (US)',
				'iso'         => array( 'en' )
			);

			$this->translations = $available_translations;
		}

		return $this->translations;
	}

	public function get_settings() {
		if ( ! $this->settings ) {
			if ( ! $this->settings ) {
				$settings = array(
					'post_types'  => array(
						'page',
						'post',
						'attachment',
						'nav_menu_item',
						'revision'
					),
					'post_fields' => array(
						'_wp_attachment_image_alt'
					),
					'taxonomies'  => array(
						'category',
						'post_tag'
					),
					'admin_pages' => array(
						'nav-menus',
						'options-general',
						'widgets',
						'settings_page_media-taxonomies'
					),
					'options'     => array(
						'blogname',
						'blogdescription'
					)
				);

				$this->settings = apply_filters( 'qtn_settings', $settings );
			}
		}

		return $this->settings;
	}
}
