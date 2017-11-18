<?php

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup language params, locales, configs on load WordPress
 *
 * Class WPM_Setup
 * @package  WPM/Includes
 * @author   Valentyn Riaboshtan
 */
class WPM_Setup {

	/**
	 * Original home url
	 *
	 * @var string
	 */
	private $original_home_url = '';

	/**
	 * Original uri
	 *
	 * @var string
	 */
	private $original_request_uri = '';

	/**
	 * Site uri
	 *
	 * @var string
	 */
	private $site_request_uri = '';

	/**
	 * Default locale
	 *
	 * @var string
	 */
	private $default_locale = '';

	/**
	 * Default site language
	 *
	 * @var string
	 */
	private $default_language = '';

	/**
	 * Languages
	 *
	 * @var array
	 */
	private $languages = array();

	/**
	 * Options
	 *
	 * @var array
	 */
	private static $options = array();

	/**
	 * Installed languages
	 *
	 * @var array
	 */
	private $installed_languages = array();

	/**
	 * User language
	 *
	 * @var string
	 */
	private $user_language = '';

	/**
	 * URL language
	 *
	 * @var string
	 */
	private $url_language = '';

	/**
	 * Available translations
	 *
	 * @var array
	 */
	private $translations = array();

	/**
	 * Config
	 *
	 * @var array
	 */
	private $config = array();

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
		add_filter( 'option_home', array( $this, 'set_home_url' ), 99 );
		if ( defined( 'DOMAIN_MAPPING' ) ) {
			add_filter( 'pre_option_home', array( $this, 'set_home_url' ), 99 );
		}
		add_action( 'after_switch_theme', array( __NAMESPACE__ . '\WPM_Config', 'load_config_run' ) );
		add_action( 'activated_plugin', array( __NAMESPACE__ . '\WPM_Config', 'load_config_run' ) );
		add_action( 'upgrader_process_complete', array( __NAMESPACE__ . '\WPM_Config', 'load_config_run' ) );
		add_action( 'wpm_init', array( $this, 'load_integrations' ) );
		add_action( 'parse_request', array( $this, 'setup_query_var' ), 0 );
		add_action( 'template_redirect', array( $this, 'redirect_default_url' ) );
		add_action( 'template_redirect', array( $this, 'redirect_to_user_language' ) );
		add_filter( 'request', array( $this, 'set_home_page' ) );
		add_filter( 'rest_url', array( $this, 'fix_rest_url' ) );
		add_filter( 'option_date_format', array( $this, 'set_date_format' ) );
		add_filter( 'option_time_format', array( $this, 'set_time_format' ) );
		add_filter( 'locale', array( $this, 'set_locale' ) );
		add_filter( 'gettext', array( $this, 'set_html_locale' ), 10, 2 );
	}


	/**
	 * Load options from base
	 *
	 * @param string $key
	 *
	 * @param string $default
	 *
	 * @return array|string
	 */
	static function get_option( $key, $default = '' ) {
		if ( ! isset( self::$options[ $key ] ) ) {
			self::$options[ $key ] = get_option( 'wpm_' . $key, $default );
		}

		return self::$options[ $key ];
	}


	/**
	 * Get original home url
	 *
	 * @since 1.7.0
	 *
	 * @param bool $unslash
	 *
	 * @return string
	 */
	public function get_original_home_url( $unslash = true ) {
		if ( ! $this->original_home_url ) {
			$home_url = home_url();
			$this->original_home_url = $unslash ? untrailingslashit( $home_url ) : $home_url;
		}

		return $this->original_home_url;
	}


	/**
	 * Get original request url
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_original_request_uri() {
		return $this->original_request_uri ? $this->original_request_uri : '/';
	}


	/**
	 * Get site request url
	 *
	 * @since 2.0.1
	 *
	 * @return string
	 */
	public function get_site_request_uri() {
		if ( ! $this->site_request_uri ) {
			$original_uri = $this->get_original_request_uri();

			if ( isset( $_GET['lang'] ) ) {
				$site_request_uri = $original_uri;
				if ( $url_lang = $this->get_lang_from_url() ) {
					$site_request_uri = str_replace( '/' . $url_lang . '/', '/', $original_uri );
				}
			} else {
				$site_request_uri = str_replace( home_url(), '', $this->get_original_home_url() . $original_uri );
			}

			$this->site_request_uri = $site_request_uri ? $site_request_uri : '/';
		}

		return $this->site_request_uri ;
	}


	/**
	 * Get installed languages
	 *
	 * @return array
	 */
	public function get_installed_languages() {
		if ( ! $this->installed_languages ) {
			$this->installed_languages = wp_parse_args( get_available_languages(), array( 'en_US' ) );
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
			$options   = self::get_option( 'languages', array() );
			$languages = array();

			foreach ( $options as $slug => $language ) {
				if ( $language['enable'] ) {
					$languages[ $slug ] = $language;
				}
			}

			$this->languages = $languages;
		}

		return $this->languages;
	}

	/**
	 * Get site locale from options
	 *
	 * @return string
	 */
	public function get_default_locale() {
		if ( ! $this->default_locale ) {
			$option_lang          = get_option( 'WPLANG' );
			$this->default_locale = $option_lang ? $option_lang : 'en_US';
		}

		return $this->default_locale;
	}

	/**
	 * Get site language
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_default_language() {
		if ( ! $this->default_language ) {
			$default_language = self::get_option( 'site_language', false );

			if ( ! $default_language ) {
				$locale           = explode( '_', $this->get_default_locale() );
				$default_language = $locale[0];
			}

			$this->default_language = $default_language;
		}

		return $this->default_language;
	}


	/**
	 * Set site locale
	 *
	 * @since 2.0.0
	 *
	 * @param $locale
	 *
	 * @return mixed
	 */
	public function set_locale( $locale ) {

		$languages = $this->get_languages();

		if ( ! $languages ) {
			return $locale;
		}

		return $languages[ $this->get_user_language() ]['translation'];
	}


	/**
	 * Get user language
	 *
	 * @return string
	 */
	public function get_user_language() {
		if ( ! $this->user_language ) {
			$this->user_language = $this->set_user_language();
		}

		return $this->user_language;
	}

	/**
	 * Set user language for frontend from url or browser
	 * Set admin language from cookie or url
	 */
	private function set_user_language() {

		$languages     = $this->get_languages();
		$url           = '';
		$user_language = '';

		require_once( ABSPATH . WPINC . '/pluggable.php' );

		if ( ! is_admin() ) {
			$url = wpm_get_current_url();
		}

		if ( wp_doing_ajax() ) {
			if ( $referrer = wp_get_raw_referer() ) {
				if ( strpos( $referrer, 'wp-admin/' ) === false ) {
					$url = $referrer;
					add_filter( 'get_user_metadata', array( $this, 'set_user_locale' ), 10, 4 );
				}
			} else {
				add_filter( 'get_user_metadata', array( $this, 'set_user_locale' ), 10, 4 );
			}
		}

		if ( $url ) {
			$this->original_request_uri = str_replace( $this->get_original_home_url(), '', $url );

			if ( $url_lang = $this->get_lang_from_url() ) {
				$user_language = $url_lang;
			}

			if ( $user_language && ! is_admin() && ! isset( $languages[ $user_language ] ) ) {
				add_action( 'template_redirect', array( $this, 'set_not_found' ) );
			}
		}

		if ( isset( $_REQUEST['lang'] ) ) {
			$lang = wpm_clean( $_REQUEST['lang'] );
			if ( isset( $languages[ $lang ] ) ) {
				$user_language = $lang;

				if ( is_admin() && ! wp_doing_ajax() ) {
					update_user_meta( get_current_user_id(), 'user_lang', $lang );
					update_user_meta( get_current_user_id(), 'locale', $languages[ $lang ]['translation'] );
				}
			} else {
				if ( ! is_admin() ) {
					add_action( 'template_redirect', array( $this, 'set_not_found' ) );
				}
			}
		} else {
			if ( is_admin() && ! wp_doing_ajax() ) {
				if ( $user_meta_language = get_user_meta( get_current_user_id(), 'user_lang', true ) ) {
					if ( isset( $languages[ $user_meta_language ] ) ) {
						$user_language = $user_meta_language;
					}
				} else {
					update_user_meta( get_current_user_id(), 'user_lang', $this->get_default_language() );
					update_user_meta( get_current_user_id(), 'locale', $this->get_default_locale() );
				}
			} elseif ( ! is_admin() && preg_match( '/^.*\.php$/i', wp_parse_url( $url, PHP_URL_PATH ) ) ) {
				if ( isset( $_COOKIE['language'] ) ) {
					$user_language = wpm_clean( $_COOKIE['language'] );
				}
			}
		}

		if ( ! $user_language || ! isset( $languages[ $user_language ] ) ) {
			$user_language = $this->get_default_language();
		}

		return $user_language;
	}


	/**
	 * Redirect to default language
	 */
	public function redirect_default_url() {
		$user_language    = $this->get_user_language();
		$default_language = $this->get_default_language();
		$url_lang         = $this->get_lang_from_url();

		if ( ! isset( $_GET['lang'] ) ) {
			if ( self::get_option( 'use_prefix', 'no' ) === 'yes' ) {
				if ( ! $url_lang ) {
					wp_redirect( home_url( $this->get_original_request_uri() ) );
					exit;
				}
			} else {
				if ( $url_lang && $user_language === $default_language ) {
					wp_redirect( home_url( preg_replace( '!^/([a-z]{2})(-[a-z]{2})?(/|$)!i', '/', $this->get_original_request_uri() ) ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['lang'] ) && ! empty( $_GET['lang'] ) && $url_lang ) {
			wp_redirect( $this->get_original_home_url() . $this->get_site_request_uri() );
			exit;
		}
	}

	/**
	 * Get available translations
	 *
	 * @return array
	 */
	public function get_translations() {
		if ( ! $this->translations ) {
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$available_translations          = wp_get_available_translations();
			$available_translations['en_US'] = array(
				'native_name' => 'English (US)',
				'iso'         => array( 'en' ),
			);

			$this->translations = $available_translations;
		}

		return $this->translations;
	}


	/**
	 * Get config from options
	 *
	 * @return array
	 */
	public function get_config() {

		if ( ! $this->config ) {
			if ( ! $config = wp_cache_get( 'wpm_config' ) ) {
				WPM_Config::load_config_run();
				$config = wp_cache_get( 'wpm_config' );
			}

			$this->config = $config;
		}

		$config            = apply_filters( 'wpm_load_config', $this->config );
		$config['options'] = apply_filters( 'wpm_options_config', $config['options'] );

		if ( is_multisite() ) {
			$config['site_options'] = apply_filters( 'wpm_site_options_config', $config['site_options'] );
		} else {
			unset( $config['site_options'] );
		}

		return $config;
	}


	/**
	 * Add lang slug to home url
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function set_home_url( $value ) {

		if ( ( is_admin() && ! wp_doing_ajax() ) || ! did_action( 'wpm_init' ) || ! $value ) {
			return $value;
		}

		$user_language    = wpm_get_user_language();
		$default_language = wpm_get_default_language();


		if ( $user_language !== $default_language || self::get_option( 'use_prefix', 'no' ) === 'yes' ) {
			$value .= '/' . $user_language;
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
	 * Load integration classes
	 */
	public function load_integrations() {

		do_action( 'wpm_integrations_init' );

		$integrations = apply_filters( 'wpm_integrations', array(
			'advanced-custom-fields'     => __NAMESPACE__ . '\Integrations\WPM_Acf',
			'advanced-custom-fields-pro' => __NAMESPACE__ . '\Integrations\WPM_Acf',
			'all-in-one-seo-pack'        => __NAMESPACE__ . '\Integrations\WPM_AIOSP',
			'better-search'              => __NAMESPACE__ . '\Integrations\WPM_Better_Search',
			'buddypress'                 => __NAMESPACE__ . '\Integrations\WPM_BuddyPress',
			'contact-form-7'             => __NAMESPACE__ . '\Integrations\WPM_CF7',
//			'gutenberg'                  => __NAMESPACE__ . '\Integrations\WPM_Gutenberg',
			'js_composer'                => __NAMESPACE__ . '\Integrations\WPM_VC',
			'mailchimp-for-wp'           => __NAMESPACE__ . '\Integrations\WPM_MailChimp_For_WP',
			'masterslider'               => __NAMESPACE__ . '\Integrations\WPM_Masterslider',
			'megamenu'                   => __NAMESPACE__ . '\Integrations\WPM_Megamenu',
			'newsletter'                 => __NAMESPACE__ . '\Integrations\WPM_Newsletter',
			'nextgen-gallery'            => __NAMESPACE__ . '\Integrations\WPM_NGG',
			'siteorigin-panels'          => __NAMESPACE__ . '\Integrations\WPM_PBSO',
			'tablepress'                 => __NAMESPACE__ . '\Integrations\WPM_Tablepress',
			'woocommerce'                => __NAMESPACE__ . '\Integrations\WPM_WooCommerce',
			'wordpress-seo'              => __NAMESPACE__ . '\Integrations\WPM_Yoast_Seo',
		) );

		foreach ( wp_cache_get( 'wpm_active_plugins' ) as $plugin ) {
			if ( isset( $integrations[ $plugin ] ) && ! empty( $integrations[ $plugin ] ) ) {
				new $integrations[ $plugin ]();
			}
		}
	}

	/**
	 * Set 404 headers for not available language
	 */
	public function set_not_found() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Add query var 'lang' in global request
	 *
	 * @param $request
	 *
	 * @return object WP
	 */
	public function setup_query_var( $request ) {
		if ( ( '/' == wp_parse_url( $this->get_site_request_uri(), PHP_URL_PATH ) ) || ( isset( $request->query_vars['paged'] ) && count( $request->query_vars ) == 1 ) || isset( $_GET['lang'] ) ) {
			return $request;
		}

		$request->query_vars['lang'] = $this->get_user_language();

		return $request;
	}

	/**
	 * Redirect to browser language
	 */
	public function redirect_to_user_language() {

		if ( ! is_admin() && ! defined( 'WP_CLI' ) ) {
			$user_language = $this->get_user_language();

			if ( ! isset( $_COOKIE['language'] ) ) {

				wpm_setcookie( 'language', $user_language, time() + YEAR_IN_SECONDS );

				if ( self::get_option( 'use_redirect', 'no' ) === 'yes' ) {

					$browser_language = $this->get_browser_language();

					if ( $browser_language && ( $browser_language !== $user_language ) ) {
						wp_redirect( wpm_translate_url( wpm_get_current_url(), $browser_language ) );
						exit;
					}
				}
			} else {
				if ( wpm_clean( $_COOKIE['language'] ) != $user_language ) {
					wpm_setcookie( 'language', $user_language, time() + YEAR_IN_SECONDS );
					do_action( 'wpm_changed_language' );
				}
			} // End if().
		} // End if().
	}


	/**
	 * Detect browser language
	 *
	 * @return string
	 */
	private function get_browser_language() {

		if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) || ! $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) {
			return '';
		}

		if ( ! preg_match_all( '#([^;,]+)(;[^,0-9]*([0-9\.]+)[^,]*)?#i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches, PREG_SET_ORDER ) ) {
			return '';
		}

		$detect             = '';
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

		$browser_languages = array_keys( $prefered_languages );
		$languages         = $this->get_languages();

		foreach ( $browser_languages as $browser_language ) {
			foreach ( $languages as $key => $value ) {
				if ( ! $locale = $value['locale'] ) {
					$locale = $value['translation'];
				}

				$locale = str_replace( '-', '_', $locale );

				if ( $browser_language == $locale || wpm_sanitize_lang_slug( $browser_language ) == $key ) {
					$detect = $key;
					break 2;
				}
			}
		}

		return $detect;
	}

	/**
	 * Fix home page if isset 'lang' GET parameter
	 *
	 * @param $query_vars
	 *
	 * @return array
	 */
	public function set_home_page( $query_vars ) {
		if ( isset( $_GET['lang'] ) && ( ( '/' == wp_parse_url( $this->get_site_request_uri(), PHP_URL_PATH ) ) || ( count( $query_vars ) == 2 && isset( $query_vars['paged'] ) ) ) ) {
			unset( $query_vars['lang'] );
		}

		return $query_vars;
	}


	/**
	 * Set user locale for AJAX front requests
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return array|string
	 */
	public function set_user_locale( $check, $object_id, $meta_key, $single ) {
		if ( 'locale' == $meta_key ) {
			if ( $single ) {
				$check = get_locale();
			} else {
				$check = array( get_locale() );
			}
		}

		return $check;
	}


	/**
	 * Fix REST url
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function fix_rest_url( $url ) {
		if ( ! self::get_option( 'use_prefix', 'no' ) === 'yes' && wpm_get_language() != wpm_get_default_language() ) {
			$url = str_replace( '/' . wpm_get_language() . '/', '/', $url );
		}

		return $url;
	}


	/**
	 * Set date format for current language
	 *
	 * @since 1.8.0
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function set_date_format( $value ) {

		require_once( ABSPATH . 'wp-admin/includes/screen.php' );

		if ( is_admin() && ! wp_doing_ajax() ) {
			$screen = get_current_screen();
			if ( $screen && 'options-general' == $screen->id ) {
				return $value;
			}
		}

		if ( defined( 'REST_REQUEST' ) && ( '/wp/v2/settings' == $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return $value;
		}

		$languages     = $this->get_languages();
		$user_language = $this->get_user_language();

		if ( isset( $languages[ $user_language ]['date'] ) && $languages[ $user_language ]['date'] ) {
			return $languages[ $user_language ]['date'];
		}

		return $value;
	}


	/**
	 * Set time format for current language
	 *
	 * @since 1.8.0
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public function set_time_format( $value ) {

		require_once( ABSPATH . 'wp-admin/includes/screen.php' );

		if ( is_admin() && ! wp_doing_ajax() ) {
			$screen = get_current_screen();
			if ( $screen && 'options-general' == $screen->id ) {
				return $value;
			}
		}

		if ( defined( 'REST_REQUEST' ) && ( '/wp/v2/settings' == $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			return $value;
		}

		$languages     = $this->get_languages();
		$user_language = $this->get_user_language();

		if ( isset( $languages[ $user_language ]['time'] ) && $languages[ $user_language ]['time'] ) {
			return $languages[ $user_language ]['time'];
		}

		return $value;
	}


	/**
	 * Set locale for html
	 *
	 * @since 2.0.0
	 *
	 * @param $translation
	 * @param $text
	 *
	 * @return mixed
	 */
	public function set_html_locale( $translation, $text ) {

		if ( 'html_lang_attribute' == $text ) {
			$languages     = $this->get_languages();
			$user_language = $this->get_user_language();

			if ( $languages && $languages[ $user_language ]['locale'] ) {
				$translation = $languages[ $user_language ]['locale'];
			}
		}

		return $translation;
	}

	/**
	 * Get lang from url
	 *
	 * @since 2.0.3
	 *
	 * @return string
	 */
	private function get_lang_from_url() {
		if ( ! $this->url_language ) {
			$url_lang = '';

			if ( preg_match( '!^/([a-z]{2})(-[a-z]{2})?(/|$)!i', $this->get_original_request_uri(), $match ) ) {
				$url_lang = $match[1];
			}

			$this->url_language = $url_lang;
		}

		return $this->url_language;
	}
}
