<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup language params, locales, configs on load WordPress
 *
 * Class WPM_Setup
 * @package  WPM\Core
 * @author   VaLeXaR
 */
class WPM_Setup {

	/**
	 * Default locale
	 *
	 * @var string
	 */
	public $default_locale = '';

	/**
	 * Languages
	 *
	 * @var array
	 */
	public $languages = array();

	/**
	 * Options
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Installed languages
	 *
	 * @var array
	 */
	public $installed_languages = array();

	/**
	 * User language
	 *
	 * @var string
	 */
	public $user_language = '';

	/**
	 * Available translations
	 *
	 * @var array
	 */
	public $translations = array();

	/**
	 * Config
	 *
	 * @var array
	 */
	public $config = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Setup
	 */
	protected static $_instance = null;

	/**
	 * Main WPM_Setup Instance.
	 *
	 * @static
	 * @return WPM_Setup - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * WPM_Setup constructor.
	 */
	public function __construct() {
		add_filter( 'query_vars', array( $this, 'set_lang_var' ) );
		add_filter( 'option_home', array( $this, 'set_home_url' ), 0 );
		add_action( 'change_locale', array( $this, 'change_locale' ), 0 );
		add_action( 'after_setup_theme', array( $this, 'setup_lang_query' ), 0 );
		add_action( 'after_switch_theme', __NAMESPACE__ . '\WPM_Config::load_config_run' );
		add_action( 'activated_plugin', __NAMESPACE__ . '\WPM_Config::load_config_run' );
		add_action( 'upgrader_process_complete', __NAMESPACE__ . '\WPM_Config::load_config_run', 10 );
		$this->init();
	}


	/**
	 * Set locale, load vendor classes
	 */
	public function init() {
		$this->set_locale();
		$this->load_vendor();
	}


	/**
	 * Load options from base
	 *
	 * @return array|string
	 */
	public function get_options() {
		if ( ! $this->options ) {
			$this->options = get_option( 'wpm_languages' );
		}

		return $this->options;
	}


	/**
	 * Get installed languages
	 *
	 * @return array
	 */
	public function get_installed_languages() {
		if ( ! $this->installed_languages ) {
			$this->installed_languages = array_merge( array( 'en_US' ), get_available_languages() );
		}

		return $this->installed_languages;
	}


	/**
	 * Get enables languages. Add installed languages to options.
	 *
	 * @return array
	 */
	public function get_languages() {
		if ( ! $this->languages ) {

			$options             = $this->get_options();
			$installed_languages = $this->get_installed_languages();

			foreach ( $options as $locale => $language ) {
				if ( $language['enable'] ) {
					$this->languages[ $locale ] = $language['slug'];
				}
			}

			foreach ( $installed_languages as $language ) {
				if ( ! in_array( $language, $this->languages ) ) {
					$translations                 = $this->get_translations();
					$this->languages[ $language ] = current( $translations[ $language ]['iso'] );
					$options[ $language ]         = array(
						'name'   => $translations[ $language ]['native_name'],
						'slug'   => current( $translations[ $language ]['iso'] ),
						'flag'   => current( $translations[ $language ]['iso'] ),
						'enable' => 1
					);
				}
			}

			if ( wpm_array_diff_recursive( $options, $this->get_options() ) ) {
				update_option( 'wpm_languages', $options );
				$this->options = $options;
			}
		}

		return $this->languages;
	}

	/**
	 * Get default locale from options
	 *
	 * @return string
	 */
	public function get_default_locale() {
		if ( ! $this->default_locale ) {
			$this->default_locale = get_option( 'WPLANG' ) ? get_option( 'WPLANG' ) : 'en_US';
		}

		return $this->default_locale;
	}


	/**
	 * Get user language
	 * @return string
	 */
	public function get_user_language() {
		if ( ! $this->user_language ) {
			$this->set_user_language();
		}

		return $this->user_language;
	}

	/**
	 * Set user language for frontend from url or browser
	 * Set admin language from cookie or url
	 */
	public function set_user_language() {

		if ( ! is_admin() || ! defined( 'REST_REQUEST' ) ) {

			$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

			if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
				$this->user_language = $match[1];
			} elseif ( ! defined( 'REST_REQUEST' ) && ! isset( $_COOKIE['wpm_first_time'] ) ) {
				$redirect_to_browser_language = apply_filters( 'wpm_redirect_to_browser_language', true );
				if ( $redirect_to_browser_language ) {
					$browser_language = $this->get_browser_language();

					if ( $browser_language != $this->user_language ) {

						$home_url   = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
						$b_home_url = $home_url . '/' . $browser_language;

						if ( $this->user_language ) {
							$home_url = $home_url . '/' . $this->user_language;
						}
						$url = str_replace( $home_url, $b_home_url, wpm_get_current_url() );
						wpm_setcookie( 'wpm_first_time', true, time() + YEAR_IN_SECONDS );
						wp_redirect( $url, 301 );
						exit;
					}
				}
			}
		}

		$languages      = $this->get_languages();
		$default_locale = $this->get_default_locale();

		if ( isset( $_GET['lang'] ) ) {
			$lang = wpm_clean( $_GET['lang'] );
			if ( in_array( $lang, $languages ) ) {
				$this->user_language = $lang;
			}

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				wpm_setcookie( 'language', $lang, time() + MONTH_IN_SECONDS );
			}

		} else {

			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				if ( isset( $_COOKIE['language'] ) ) {
					$lang = wpm_clean( $_COOKIE['language'] );
					if ( in_array( $lang, $languages ) ) {
						$this->user_language = $lang;
					}
				} else {
					wpm_setcookie( 'language', $languages[ $default_locale ], time() + MONTH_IN_SECONDS );
				}
			}
		}
	}


	/**
	 * Set locale from user language
	 */
	public function set_locale() {

		if ( ! did_action( 'before_wpm_init' ) ) {
			return;
		}

		$languages      = $this->get_languages();
		$default_locale = $this->get_default_locale();

		foreach ( $languages as $key => $value ) {
			$user_language = $this->get_user_language();
			if ( ( $value == $user_language ) ) {
				switch_to_locale( $key );
				if ( $key == $default_locale && ! is_admin() && ! isset( $_GET['lang'] ) ) {
					wp_redirect( home_url( str_replace( '/' . $user_language . '/', '/', $_SERVER['REQUEST_URI'] ) ), 301 );
					exit;
				}
				break;
			}
		}

		if ( ! $this->user_language ) {
			$this->user_language = $languages[ $default_locale ];
		}
	}

	/**
	 * Get available translations
	 * @return array
	 */
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


	/**
	 * Get config from options
	 * @return array
	 */
	public function get_config() {
		if ( ! $this->config ) {
			$config       = get_option( 'wpm_config' );
			$this->config = apply_filters( 'wpm_load_config', $config );
		}

		return $this->config;
	}


	/**
	 * Add 'lang' param to query vars
	 */
	public function setup_lang_query() {
		$user_language = $this->get_user_language();
		set_query_var( 'lang', $user_language );
		add_filter( 'request', function ( $query_vars ) {
			$query_vars['lang'] = get_query_var( 'lang' );

			return $query_vars;
		} );
	}

	/**
	 * Set global $locale when change locale
	 *
	 * @param $new_locale
	 */
	public function change_locale( $new_locale ) {
		global $locale;
		$locale = $new_locale;
	}


	/**
	 * Add lang slug to home url
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function set_home_url( $value ) {
		$language       = $this->get_user_language();
		$languages      = $this->get_languages();
		$default_locale = $this->get_default_locale();
		if ( $language != $languages[ $default_locale ] ) {
			$value .= '/' . $language;
		}

		return $value;
	}


	/**
	 * Add 'lang' param to allow params
	 *
	 * @param $public_query_vars
	 *
	 * @return array
	 */
	public function set_lang_var( $public_query_vars ) {
		$public_query_vars[] = 'lang';

		return $public_query_vars;
	}


	/**
	 * Load vendor classes
	 */
	private function load_vendor() {
		$vendor_path = ( dirname( WPM_PLUGIN_FILE ) . '/core/vendor/' );
		foreach ( glob( $vendor_path . '*.php' ) as $vendor_file ) {
			require_once( $vendor_file );
		}
	}


	/**
	 * Detect browser language
	 *
	 * @return null|string
	 */
	private function get_browser_language() {
		if ( ! isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ) {
			return null;
		}

		if ( ! preg_match_all( "#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $matches, PREG_SET_ORDER ) ) {
			return null;
		}

		$prefered_languages = array();
		$priority           = 1.0;

		foreach ( $matches as $match ) {
			if ( ! isset( $match[3] ) ) {
				$pr       = $priority;
				$priority -= 0.001;
			} else {
				$pr = floatval( $match[3] );
			}
			$prefered_languages[ str_replace( '-', '_', $match[1] ) ] = $pr;
		}
		arsort( $prefered_languages, SORT_NUMERIC );

		$languages = $this->get_languages();

		foreach ( $prefered_languages as $language => $priority ) {
			if ( in_array( $language, $languages ) || isset( $languages[ $language ] ) ) {
				return $language;
			}
		}

		return null;
	}
}
