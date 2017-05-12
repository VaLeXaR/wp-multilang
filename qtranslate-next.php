<?php
/**
 * Plugin Name:     qTranslate-Next
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Valentyn Riaboshtan
 * Author URI:      YOUR SITE HERE
 * Text Domain:     qtranslate-next
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package  Qtranslate_Next
 * @category Core
 * @author   Valentyn Riaboshtan
 */

use QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/lib/autoloader.php';
require_once __DIR__ . '/vendor/autoload.php';

if ( WP_DEBUG ) {
	if ( class_exists( 'Kint' ) ) {
		Kint::$enabled_mode = false;
	}

	require_once 'debug.php';
}


if ( ! class_exists( 'Qtranslate_Next' ) ) :

	/**
	 * Main Qtranslate_Next Class.
	 *
	 * @class   QtranslateNext
	 * @version 1.0.0
	 */
	final class Qtranslate_Next {

		/**
		 * QtranslateNext version.
		 *
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * The single instance of the class.
		 *
		 * @var Qtranslate_Next
		 * @since 2.1
		 */
		protected static $_instance = null;

		/**
		 * Order factory instance.
		 *
		 * @var Core\QtN_Config
		 */
		public $config = null;

		/**
		 * Main Qtranslate_Next Instance.
		 *
		 * Ensures only one instance of WooCommerce is loaded or can be loaded.
		 *
		 * @static
		 * @see   QN()
		 * @return Qtranslate_Next - Main instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 * @since 2.1
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '2.1' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 2.1
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce' ), '2.1' );
		}

		/**
		 * WooCommerce Constructor.
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();

			do_action( 'qtn_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 * @since  2.3
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( 'QtNext\Core\QtN_Install', 'install' ) );
			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * Define WC Constants.
		 */
		private function define_constants() {
			$this->define( 'QTN_PLUGIN_FILE', __FILE__ );
			$this->define( 'QTN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'QTN_VERSION', $this->version );
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
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				default:
					return false;
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
			include_once( 'core/qtn-core-functions.php' );
			include_once( 'core/qtn-core-hooks.php' );
//			include_once( 'core/wc-widget-functions.php' );
			$this->config = new Core\QtN_Config();
			$this->config->set_locale();
			Core\QtN_AJAX::init();

			include_once( 'core/abstracts/abstract-qtn-object.php' );
			new Core\QtN_Posts();
			new Core\QtN_Taxonomies();
			new Core\QtN_Options();
//			new Core\QtN_Widgets();

			if ( $this->is_request( 'admin' ) ) {
				new Core\Admin\QtN_Admin;
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}
		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
			include_once( 'core/qtn-template-hooks.php' );
//			include_once( 'core/class-wc-frontend-scripts.php' );               // Frontend Scripts
		}

		/**
		 * Init WooCommerce when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_qtn_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'qtn_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'qtranslate-next', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
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
		 * Get the plugin path.
		 * @return string
		 */
		public function template_path() {
			return $this->plugin_path() . '/templates/';
		}

		/**
		 * Get the plugin path.
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

function QN() {
	return Qtranslate_Next::instance();
}

QN();
