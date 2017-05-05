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
	public $default_lang = '';

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $available_languages = array();

	/**
	 * Order factory instance.
	 *
	 * @var string
	 */
	public $user_language = '';

	/**
	 * Order factory instance.
	 *
	 * @var string
	 */
	public $user_locale = '';

	/**
	 * Order factory instance.
	 *
	 * @var array
	 */
	public $languages = array();

	/**
	 * WooCommerce Constructor.
	 */
	public function __construct() {
		$this->setup_languages();
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		add_action( 'after_setup_theme', array( $this, 'setup_lang_query' ) );
		add_action( 'change_locale', array( $this, 'change_locale' ) );
		add_action( 'wp_head', array( $this, 'set_meta_languages' ) );

		add_filter( 'option_home', array( $this, 'set_home_url' ) );
		add_filter( 'query_vars', array( $this, 'set_lang_var' ) );
	}

	public function setup_languages() {

		$this->default_lang = get_option( 'WPLANG' );

		if ( ! $this->user_language ) {
			$path = $_SERVER['REQUEST_URI'];

			if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
				$this->user_language = $match[1];
			}
		}

		$this->languages           = $this->get_translations();
		$this->available_languages = get_option( 'qtn_available_languages' );
		$this->set_locale();
	}

	private function get_translations() {

		require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		$available_translations = array_merge_recursive( array(
			'en_US' => array(
				'iso' => array( 'en' )
			)
		), wp_get_available_translations() );

		return $available_translations;
	}

	private function get_current_lang() {
		global $locale;
		$current_lang = $this->available_languages[ $locale ]['slug'];

		return $current_lang;
	}

	public function set_locale() {
		global $locale;

		foreach ( $this->available_languages as $key => $value ) {

			if ( ( $value['slug'] == $this->user_language ) && ! $value['disable'] ) {
				$locale = $key;
				if ( $locale == $this->default_lang ) {
					require_once( ABSPATH . 'wp-includes/pluggable.php' );
					wp_redirect( home_url( str_replace( '/' . $this->user_language . '/', '/', $_SERVER['REQUEST_URI'] ) ), 301 );
					exit;
				}
				break;
			}
		}
	}

	public function setup_lang_query() {
		set_query_var( 'lang', $this->get_current_lang() );
		add_filter( 'request', function ( $query_vars ) {
			$query_vars['lang'] = get_query_var( 'lang' );

			return $query_vars;
		} );
	}

	public function set_home_url( $value ) {
		if ( defined( 'DOING_AJAX' ) || defined( 'REST_REQUEST' ) ) {
			return $value;
		}

		$lang = $this->get_current_lang();
		if ( $lang != $this->default_lang ) {
			$value .= '/' . $lang;
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

	public function set_meta_languages() {
		foreach ( $this->available_languages as $locale => $language ) {
			printf( '<link rel="alternate" hreflang="%s" href="%s"/>', $language['slug'], home_url( $language['slug'] ) );
		}
	}
}
