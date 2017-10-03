<?php
/**
 * Plugin Name:     WP Multilang
 * Plugin URI:      https://github.com/VaLeXaR/wp-multilang
 * Description:     Multilingual plugin for WordPress
 * Author:          Valentyn Riaboshtan
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     wpm
 * Domain Path:     /languages
 * Version:         1.7.3
 *
 * @package  WPM
 * @category Core
 * @author   Valentyn Riaboshtan
 */

use WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/lib/autoload.php';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( 'WP_Multilang' ) ) :

	/**
	 * Main WP_Multilang.
	 *
	 * @class   WPM
	 * @version 1.0.5
	 */
	final class WP_Multilang {

		/**
		 * WP Multilang version.
		 *
		 * @var string
		 */
		public $version = '1.7.3';

		/**
		 * The single instance of the class.
		 *
		 * @var WP_Multilang
		 */
		protected static $_instance = null;

		/**
		 * Main WP_Multilang Instance.
		 *
		 * Ensures only one instance of WP Multilang is loaded or can be loaded.
		 *
		 * @static
		 * @see   WPM()
		 * @return WP_Multilang - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpm' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wpm' ), '1.0' );
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
			register_activation_hook( __FILE__, array( 'WPM\Core\WPM_Install', 'install' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'after_setup_theme', array( $this, 'translate_options' ), 1 );
		}

		/**
		 * Define WPM Constants.
		 */
		private function define_constants() {
			$this->define( 'WPM_PLUGIN_FILE', __FILE__ );
			$this->define( 'WPM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
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
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return wp_doing_ajax();
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || wp_doing_ajax() ) && ! defined( 'DOING_CRON' );
				default:
					return false;
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'core/wpm-core-functions.php' );
			include_once( 'core/wpm-widget-functions.php' );

			Core\WPM_Install::init();
			Core\WPM_Setup::instance();

			if ( $this->is_request( 'frontend' ) ) {
				include_once( 'core/wpm-template-hooks.php' );
				Core\WPM_Frontend_Scripts::init();               // Frontend Scripts
			}
		}

		/**
		 * Translate options
		 */
		public function translate_options() {
			new Core\WPM_Options();

			if ( is_multisite() ) {
				new Core\WPM_Site_Options();
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

			Core\WPM_AJAX::init();
			new Core\WPM_Menus();
			new Core\WPM_Posts();
			new Core\WPM_Taxonomies();
			new Core\WPM_Widgets();
			new Core\WPM_Users();
			new Core\WPM_Comments();

			if ( $this->is_request( 'admin' ) ) {
				new Core\Admin\WPM_Admin;
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
			load_plugin_textdomain( 'wpm', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the plugin url.
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
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
		public function flag_dir() {
			return $this->plugin_url() . '/flags/';
		}

		/**
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
	}

endif;

function WPM() {
	return WP_Multilang::instance();
}

WPM();
