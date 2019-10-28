<?php

/**
 * Main WP_Multilang.
 *
 * @class   WP_Multilang
 * @version 1.1.1
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WP_Multilang {

	/**
	 * WP Multilang version.
	 *
	 * @var string
	 */
	public $version = '2.4.1';

	/**
	 * The single instance of the class.
	 *
	 * @var WP_Multilang
	 */
	protected static $_instance = null;

	/**
	 * Setup instance.
	 *
	 * @var WPM_Setup
	 */
	public $setup = null;

	/**
	 * Main WP_Multilang Instance.
	 *
	 * Ensures only one instance of WP Multilang is loaded or can be loaded.
	 *
	 * @static
	 * @see   wpm()
	 * @return WP_Multilang - Main instance.
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-multilang' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-multilang' ), '1.0' );
	}

	/**
	 * Multilang Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'wpm_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'WPM\Includes\WPM_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'after_setup_theme', array( $this, 'translate_options' ), 1 );
	}

	/**
	 * Define WPM Constants.
	 */
	private function define_constants() {
		$this->define( 'WPM_ABSPATH', dirname( WPM_PLUGIN_FILE ) . '/' );
		$this->define( 'WPM_PLUGIN_BASENAME', plugin_basename( WPM_PLUGIN_FILE ) );
		$this->define( 'WPM_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string      $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return wp_doing_ajax();
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || wp_doing_ajax() ) && ! defined( 'DOING_CRON' );
			default:
				return false;
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Core classes.
		 */
		include_once WPM_ABSPATH . 'includes/wpm-core-functions.php';
		include_once WPM_ABSPATH . 'includes/wpm-widget-functions.php';

		WPM_Install::init();
		$this->setup = new WPM_Setup();

		if ( $this->is_request( 'frontend' ) ) {
			WPM_Frontend_Scripts::init(); // Frontend Scripts
		}
	}

	/**
	 * Translate options
	 */
	public function translate_options() {
		new WPM_Options();

		if ( is_multisite() ) {
			new WPM_Site_Options();
		}
	}

	/**
	 * Init Multilang when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_wpm_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		WPM_AJAX::init();
		new WPM_REST_Settings();
		new WPM_Menus();
		new WPM_Posts();
		new WPM_Taxonomies();
		new WPM_Widgets();
		new WPM_Users();
		new WPM_Comments();
		new WPM_Shortcodes();

		if ( $this->is_request( 'admin' ) ) {
			new Admin\WPM_Admin();
		}

		// Init action.
		do_action( 'wpm_init' );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-multilang', false, plugin_basename( dirname( WPM_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WPM_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WPM_PLUGIN_FILE ) );
	}

	/**
	 * Get the templates path.
	 * @return string
	 */
	public function template_path() {
		return $this->plugin_path() . '/templates/';
	}

	/**
	 * Get the flags path.
	 * @return string
	 */
	public function flags_dir() {
		return $this->plugin_url() . '/flags/';
	}

	/**
	 * Get the flags path.
	 * @return string
	 */
	public function flags_path() {
		return $this->plugin_path() . '/flags/';
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}
}
