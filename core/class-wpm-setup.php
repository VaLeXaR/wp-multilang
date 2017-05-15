<?php
/**
 *
 * @class   WPM_Setup
 */

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Setup {

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
	public $config = array();

	/**
	 * The single instance of the class.
	 *
	 * @var WPM_Setup
	 */
	protected static $_instance = null;

	/**
	 * Main WPM_Config Instance.
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

	public function init() {
		$this->set_locale();
		$this->load_vendor();
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
				if ( $key == $default_locale && ! is_admin() && ! isset( $_GET['lang'] ) ) {
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

	public function get_config() {
		if ( ! $this->config ) {
			$this->config = get_option( 'wpm_config' );
		}

		return $this->config;
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

		//TODO set cookie for ajax?

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


	private function load_vendor() {
		$vendor_path = ( dirname( WPM_PLUGIN_FILE ) . '/configs/core/vendor/' );
		foreach ( glob( $vendor_path . '*.php' ) as $vendor_file ) {
			require_once( $vendor_file );
		}
	}
}
