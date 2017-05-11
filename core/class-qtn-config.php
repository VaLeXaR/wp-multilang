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

	/**
	 * WooCommerce Constructor.
	 */
	public function __construct() {
		$this->setup_languages();
		$this->init_hooks();
	}


	public function setup_languages() {

		require_once( ABSPATH . 'wp-includes/pluggable.php' );

		$this->default_locale = get_option( 'WPLANG' );
		$this->options        = get_option( 'qtn_languages' );

		foreach ( $this->options as $locale => $language ) {
			if ( $language['enable'] ) {
				$this->languages[ $locale ] = $language['slug'];
			}
		}

		$this->installed_languages = array_merge( array( 'en_US' ), get_available_languages() );
		$this->translations        = $this->get_translations();
		$this->set_user_lang();
		$this->set_locale();
	}


	private function set_user_lang() {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			if ( isset( $_GET['lang'] ) ) {
				qtn_setcookie( 'language', qtn_clean( $_GET['lang'] ), time() + MONTH_IN_SECONDS );
			}

			if ( ! isset( $_COOKIE['language'] ) ) {
				qtn_setcookie( 'language', $this->default_locale, time() + MONTH_IN_SECONDS );
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


	private function set_locale() {
		global $locale;

		foreach ( $this->languages as $key => $value ) {

			if ( ( $value == $this->user_language ) ) {
				$locale = $key;
				if ( $locale == $this->default_locale && ! is_admin() && ! isset( $_GET['lang'] ) ) {
					wp_redirect( home_url( str_replace( '/' . $this->user_language . '/', '/', $_SERVER['REQUEST_URI'] ) ), 301 );
					exit;
				}
				break;
			}
		}

		if ( ! $this->user_language ) {
			$this->user_language = $this->languages[ $this->default_locale ];
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'after_setup_theme', array( $this, 'setup_lang_query' ) );
		add_action( 'change_locale', array( $this, 'change_locale' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_config' ) );
		add_filter( 'option_home', array( $this, 'set_home_url' ) );
		add_filter( 'query_vars', array( $this, 'set_lang_var' ) );
	}


	private function get_translations() {

		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

		$available_translations = wp_get_available_translations();

		$available_translations['en_US'] = array(
			'native_name' => 'English (US)',
			'iso'         => array( 'en' )
		);

		return $available_translations;
	}


	public function setup_lang_query() {
		set_query_var( 'lang', $this->user_language );
		add_filter( 'request', function ( $query_vars ) {
			$query_vars['lang'] = get_query_var( 'lang' );

			return $query_vars;
		} );
	}


	public function set_home_url( $value ) {
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || defined( 'REST_REQUEST' ) ) {
			return $value;
		}

		//TODO set cookie for ajax

		$locale = get_locale();
		if ( $this->languages[ $locale ] != $this->languages[ $this->default_locale ] ) {
			$value .= '/' . $this->languages[ $locale ] . '/';
		}

		return $value;
	}


	public function set_lang_var( $public_query_vars ) {
		$public_query_vars[] = 'lang';

		return $public_query_vars;
	}


	public function change_locale( $new_locale ) {
		global $locale;
		$locale = $new_locale;
	}


	public function setup_config() {
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
				'options-general',
				'widgets',
				'settings_page_media-taxonomies'
			)
		);

		$this->settings = apply_filters( 'qtn_settings', $settings );
	}
}
