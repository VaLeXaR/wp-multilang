<?php
namespace QtNext\Core;

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
		 * @var array
		 */
		public $available_locales = array();

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
			$this->setup_languages();
			$this->includes();
			$this->init_hooks();

			do_action( 'qtn_loaded' );
		}

		/**
		 * Hook into actions and filters.
		 * @since  2.3
		 */
		private function init_hooks() {
			add_action( 'after_setup_theme', array( $this, 'setup_lang_query' ) );
			add_action('plugins_loaded', array($this, 'set_locale'), PHP_INT_MAX);
			add_action( 'init', array( $this, 'init' ), 0 );

			add_filter( 'option_home', array($this, 'set_home_url'));
			add_filter( 'query_vars', array($this, 'set_lang_var'));
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
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {
//			include_once( 'includes/class-wc-autoloader.php' );
//			include_once( 'includes/wc-core-functions.php' );
//			include_once( 'includes/wc-widget-functions.php' );
//			include_once( 'includes/wc-webhook-functions.php' );
//			include_once( 'includes/class-wc-install.php' );
//			include_once( 'includes/class-wc-geolocation.php' );
//			include_once( 'includes/class-wc-download-handler.php' );
//			include_once( 'includes/class-wc-comments.php' );
//			include_once( 'includes/class-wc-post-data.php' );
//			include_once( 'includes/class-wc-ajax.php' );

			if ( $this->is_request( 'admin' ) ) {
//				include_once( 'includes/admin/class-wc-admin.php' );
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

			if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {
//				include_once( 'includes/class-wc-session-handler.php' );
			}

//			include_once( 'includes/class-wc-query.php' ); // The main query class
//			include_once( 'includes/class-wc-api.php' ); // API Class
//			include_once( 'includes/class-wc-auth.php' ); // Auth Class
//			include_once( 'includes/class-wc-post-types.php' ); // Registers post types
//			include_once( 'includes/abstracts/abstract-wc-data.php' );                 // WC_Data for CRUD
//			include_once( 'includes/abstracts/abstract-wc-payment-token.php' ); // Payment Tokens
//			include_once( 'includes/abstracts/abstract-wc-product.php' ); // Products
//			include_once( 'includes/abstracts/abstract-wc-order.php' ); // Orders
//			include_once( 'includes/abstracts/abstract-wc-settings-api.php' ); // Settings API (for gateways, shipping, and integrations)
//			include_once( 'includes/abstracts/abstract-wc-shipping-method.php' ); // A Shipping method
//			include_once( 'includes/abstracts/abstract-wc-payment-gateway.php' ); // A Payment gateway
//			include_once( 'includes/abstracts/abstract-wc-integration.php' ); // An integration with a service
//			include_once( 'includes/class-wc-product-factory.php' ); // Product factory
//			include_once( 'includes/class-wc-payment-tokens.php' ); // Payment tokens controller
//			include_once( 'includes/gateways/class-wc-payment-gateway-cc.php' ); // CC Payment Gateway
//			include_once( 'includes/gateways/class-wc-payment-gateway-echeck.php' ); // eCheck Payment Gateway
//			include_once( 'includes/class-wc-countries.php' ); // Defines countries and states
//			include_once( 'includes/class-wc-integrations.php' ); // Loads integrations
//			include_once( 'includes/class-wc-cache-helper.php' ); // Cache Helper
//			include_once( 'includes/class-wc-https.php' ); // https Helper

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
//				include_once( 'includes/class-wc-cli.php' );
			}

//			$this->query = new WC_Query();
//			$this->api   = new WC_API();
		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
//			include_once( 'includes/wc-cart-functions.php' );
//			include_once( 'includes/wc-notice-functions.php' );
//			include_once( 'includes/wc-template-hooks.php' );
//			include_once( 'includes/class-wc-template-loader.php' );                // Template Loader
//			include_once( 'includes/class-wc-frontend-scripts.php' );               // Frontend Scripts
//			include_once( 'includes/class-wc-form-handler.php' );                   // Form Handlers
//			include_once( 'includes/class-wc-cart.php' );                           // The main cart class
//			include_once( 'includes/class-wc-tax.php' );                            // Tax class
//			include_once( 'includes/class-wc-shipping-zones.php' );                 // Shipping Zones class
//			include_once( 'includes/class-wc-customer.php' );                       // Customer class
//			include_once( 'includes/class-wc-shortcodes.php' );                     // Shortcodes class
//			include_once( 'includes/class-wc-embed.php' );                          // Embeds
		}

		/**
		 * Init WooCommerce when WordPress Initialises.
		 */
		public function init() {
			// Before init action.
			do_action( 'before_qtn_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Load class instances.
//			$this->product_factory = new WC_Product_Factory();                      // Product Factory to create new product instances
//			$this->order_factory   = new WC_Order_Factory();                        // Order Factory to create new order instances
//			$this->countries       = new WC_Countries();                            // Countries class
//			$this->integrations    = new WC_Integrations();                         // Integrations class

			// Session class, handles session data for users - can be overwritten if custom handler is needed.
			if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {
//				$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
//				$this->session = new $session_class();
			}

			// Classes/actions loaded for the frontend and for ajax requests.
			if ( $this->is_request( 'frontend' ) ) {
//				$this->cart     = new WC_Cart();                                    // Cart class, stores the cart contents
//				$this->customer = new WC_Customer();                                // Customer class, handles data such as customer location
			}

			// Init action.
			do_action( 'qtn_init' );
		}


		public function set_locale() {
			global $locale;
			$available_languages = array_merge( array( 'en_US' ), get_available_languages() );

			foreach ($available_languages as $language) {
				$current_lang = current( $this->languages[ $language ]['iso'] );

				if ($current_lang == $this->user_language) {
					$locale = $language;
					if ($language == get_option( 'WPLANG')) {
						wp_redirect( home_url(str_replace( '/' . $this->user_language . '/', '/', $_SERVER['REQUEST_URI'])), 301);
						exit;
					}
					break;
				}
			}
		}

		public function setup_languages(){

			if ( ! $this->user_language) {
				$path = $_SERVER['REQUEST_URI'];

				if ( preg_match( '!^/([a-z]{2})(/|$)!i', $path, $match ) ) {
					$this->user_language = $match[1];
				}
			}

			if ( ! $this->languages ) {
				$this->languages = $this->get_translations();
			}
		}

		private function get_translations(){

			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$available_translations = array_merge_recursive( array(
				'en_US' => array(
					'iso'      => array( 'en' )
				)
			), wp_get_available_translations() );

			return $available_translations;
		}

		private function get_current_lang() {
			global $locale;
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			$available_translations = array_merge_recursive( array(
				'en_US' => array(
					'iso'      => array( 'en' )
				)
			), wp_get_available_translations() );
			$current_lang = current( $available_translations[ $locale ]['iso'] );
			return $current_lang;
		}


		public function setup_lang_query(){
			set_query_var( 'lang', $this->get_current_lang() );
			add_filter( 'request', function( $query_vars ) {
				$query_vars['lang'] = get_query_var( 'lang' );
				return $query_vars;
			});
		}

		public function set_home_url($value){
			if ( defined('DOING_AJAX') || defined('REST_REQUEST') ) {
				return $value;
			}

			$lang = $this->get_current_lang();
			if ($lang != get_option( 'WPLANG')) {
				$value .= '/' . $lang;
			}

			return $value;
		}


		public function set_lang_var($public_query_vars){
			$public_query_vars[] = 'lang';
			return $public_query_vars;
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/woocommerce/woocommerce-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/woocommerce-LOCALE.mo
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
		 * Get Ajax URL.
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
	}

endif;
