<?php
/**
 *
 * @class   WPM_Config
 */

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Config {

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

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Config
	 */
	protected static $_instance = null;

	/**
	 * Main WPM_Config Instance.
	 *
	 * @static
	 * @return WPM_Config - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {
		add_filter( 'query_vars', array( $this, 'set_lang_var' ) );
		add_filter( 'option_home', array( $this, 'set_home_url' ), 0 );
		add_action( 'change_locale', array( $this, 'change_locale' ), 0 );
		add_action( 'after_setup_theme', array( $this, 'setup_lang_query' ), 0 );
		//add_action( 'after_setup_theme', array( $this, 'set_settings' ), 0 );
		$this->set_locale();
	}


	public function get_options() {
		if ( ! $this->options ) {
			$this->options = get_option( 'wpm_languages' );
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
			$this->default_locale = get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : 'en_US';
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
				$lang      = wpm_clean( $_GET['lang'] );
				if ( ! in_array( $lang, $languages ) ) {
					$lang = $languages[ $default_locale ];
				}
				wpm_setcookie( 'language', $lang, time() + MONTH_IN_SECONDS );
			}

			if ( ! isset( $_COOKIE['language'] ) ) {
				wpm_setcookie( 'language', $default_locale, time() + MONTH_IN_SECONDS );
				$this->user_language = $this->languages[ $default_locale ];
			} else {
				$this->user_language = wpm_clean( $_COOKIE['language'] );
			}

		} else {
			$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

			if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
				$this->user_language = $match[1];
			}
		}

		if ( isset( $_GET['lang'] ) ) {
			$this->user_language = wpm_clean( $_GET['lang'] );
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
						'nav_menu_item'
					),
					'post_fields' => array(
						'_wp_attachment_image_alt'
					),
					'taxonomies'  => array(
						'category',
						'post_tag'
					),
					'tax_fields' => array(),
					'admin_pages' => array(
						'upload',
						'nav-menus',
						'options-general',
						'widgets'
					),
					'options'     => array(
						'blogname',
						'blogdescription'
					),
					'widgets' => array()
				);

				$this->settings = apply_filters( 'wpm_settings', $settings );
			}
		}

		return $this->settings;
	}


	public function setup_lang_query() {
		$user_language = $this->get_user_language();
		set_query_var( 'lang', $user_language );
		add_filter( 'request', function ( $query_vars ) {
			$query_vars['lang'] = get_query_var( 'lang' );

			return $query_vars;
		} );
	}


	public function change_locale( $new_locale ) {
		global $locale;
		$locale = $new_locale;
	}


	public function set_home_url( $value ) {
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || defined( 'REST_REQUEST' ) ) {
			return $value;
		}

		//TODO set cookie for ajax

		$locale         = get_locale();
		$languages      = $this->get_languages();
		$default_locale = $this->get_default_locale();
		if ( $languages[ $locale ] != $languages[ $default_locale ] ) {
			$value .= '/' . $languages[ $locale ];
		}

		return $value;
	}


	public function set_lang_var( $public_query_vars ) {
		$public_query_vars[] = 'lang';

		return $public_query_vars;
	}
}
