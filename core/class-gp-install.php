<?php
/**
 * Installation related functions and actions.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  GamePortal/Classes
 */

namespace GP;

use GP\Admin\GP_Admin_Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GP_Install Class.
 */
class GP_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'1.0.0' => array(
			'gp_update_100_backgrounds',
			'gp_update_100_options',
			'gp_update_100_previews',
			'gp_update_100_games',
			'gp_update_100_levels',
			'gp_update_100_orders',
			'gp_update_100_users',
			'gp_update_100_clear_db',
			'gp_update_100_update_invoices',
			'gp_update_100_move_invoices',
			'gp_update_100_db_version',
		),
	);

	/** @var object Background update class */
	private static $background_updater;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'acf/init', array( __CLASS__, 'settings_init' ) );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_action( 'admin_init', array( __CLASS__, 'check_dependence' ) );
		add_filter( 'plugin_action_links_' . GP_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
		add_action( 'game_portal_plugin_background_installer', array( __CLASS__, 'background_installer' ), 10, 2 );
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		include_once( 'class-gp-background-updater.php' );
		self::$background_updater = new GP_Background_Updater();
	}

	/**
	 * Check GamePortal version and run the updater is required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		if ( get_option( 'game_portal_version' ) !== GP()->version ) {
			self::install();
		}
	}


	public static function settings_init() {
		acf_add_options_sub_page( array(
			'page_title'  => __( 'Game Backgrounds', 'game-portal' ),
			'menu_title'  => __( 'Backgrounds', 'game-portal' ),
			'parent_slug' => 'game-portal',
			'capability'  => 'manage_game_portal'
		) );

		acf_add_local_field_group( array(
			'key'      => 'game_portal_background',
			'title'    => 'Backgrounds',
			'fields'   => array(
				array(
					'key'   => 'field_game_portal_backgrounds',
					'label' => 'Backgrounds',
					'name'  => 'game_portal_backgrounds',
					'type'  => 'gallery'
				)
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'acf-options-backgrounds',
					),
				),
			)
		) );

		acf_update_setting( 'google_api_key', get_option( 'game_portal_google_api_key' ) );
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_game_portal'] ) ) {
			self::update();
			GP_Admin_Notices::add_notice( 'update' );
		}
		if ( ! empty( $_GET['force_update_game_portal'] ) ) {
			do_action( 'wp_gp_updater_cron' );
			wp_safe_redirect( admin_url( 'admin.php?page=gp-settings' ) );
		}
	}


	public static function check_dependence() {
		if ( is_plugin_inactive( 'wordpress-simple-paypal-shopping-cart/wp_shopping_cart.php' ) ) {
			add_action('admin_notices', function() { ?>
				<div class="notice notice-error"><p><?php echo sprintf( __('You must activate %sWordPress Simple Paypal Shopping Cart plugin%s', 'game-portal'), '<a href="' .esc_url( 'https://wordpress.org/plugins/wordpress-simple-paypal-shopping-cart/') .'">', '</a>'); ?></p></div>
			<?php } );
			deactivate_plugins( GP_PLUGIN_BASENAME );
		}
	}

	/**
	 * Install GP.
	 */
	public static function install() {
		global $wpdb;

		if ( ! defined( 'GP_INSTALLING' ) ) {
			define( 'GP_INSTALLING', true );
		}

		// Ensure needed classes are loaded
		GP_Admin_Notices::init();

		self::create_options();
		self::create_tables();
		self::create_roles();

		// Register post types
		GP_Post_Types::register_post_types();
		GP_Post_Types::register_taxonomies();

		//Register rewrite rules
		GP_Post_Types::register_rewrite_rules();

		// Also register endpoints - this needs to be done prior to rewrite rule flush
		GP()->query->init_query_vars();
		GP()->query->add_endpoints();
		GP_API::add_endpoint();

		self::create_cron_jobs();
		self::create_files();
		self::create_pages();
		self::gp_setup_plugins_save();

		// Queue upgrades/setup wizard
		$current_db_version = get_option( 'game_portal_db_version', null );

		GP_Admin_Notices::remove_all_notices();

		if ( version_compare( $current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
			GP_Admin_Notices::add_notice( 'update' );
		} else {
			self::update_db_version();
		}

		self::update_gp_version();

		// Flush rules after install
		flush_rewrite_rules();

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) );

		// Trigger action
		do_action( 'game_portal_installed' );
	}

	/**
	 * Update GP version to current.
	 */
	private static function update_gp_version() {
		delete_option( 'game_portal_version' );
		add_option( 'game_portal_version', GP()->version );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {
		$current_db_version = get_option( 'game_portal_db_version' );
		$update_queued      = false;
		foreach ( self::$db_updates as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}
		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'game_portal_db_version' );
		add_option( 'game_portal_db_version', is_null( $version ) ? GP()->version : $version );
	}

	/**
	 * Add more cron schedules.
	 *
	 * @param  array $schedules
	 *
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Monthly', 'game-portal' )
		);

		return $schedules;
	}

	/**
	 * Create cron jobs (clear them first).
	 */
	private static function create_cron_jobs() {
		wp_clear_scheduled_hook( 'game_portal_cleanup_sessions' );

		wp_schedule_event( time(), 'twicedaily', 'game_portal_cleanup_sessions' );
	}

	/**
	 * Create pages that the plugin relies on, storing page id's in variables.
	 */
	public static function create_pages() {
		include_once( 'admin/gp-admin-functions.php' );

		$pages = array(
			'games'     => array(
				'name'    => _x( 'games', 'Page slug', 'game-portal' ),
				'title'   => _x( 'Games', 'Page title', 'game-portal' ),
				'content' => ''
			),
			'myaccount' => array(
				'name'    => _x( 'my-account', 'Page slug', 'game-portal' ),
				'title'   => _x( 'My Account', 'Page title', 'game-portal' ),
				'content' => '[game_portal_my_account]'
			)
		);

		foreach ( $pages as $key => $page ) {
			gp_create_page( esc_sql( $page['name'] ), 'game_portal_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? gp_get_page_id( $page['parent'] ) : '' );
		}

		delete_transient( 'game_portal_cache_excluded_uris' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		$settings = Admin\GP_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );
	}

	/**
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */

		$tables = "
CREATE TABLE {$wpdb->prefix}game_portal_sessions (
  session_id bigint(20) NOT NULL AUTO_INCREMENT,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry bigint(20) NOT NULL,
  PRIMARY KEY  (session_key),
  UNIQUE KEY session_id (session_id)
) $collate;
CREATE TABLE {$wpdb->prefix}game_portal_api_keys (
  key_id bigint(20) NOT NULL auto_increment,
  user_id bigint(20) NOT NULL,
  description longtext NULL,
  permissions varchar(10) NOT NULL,
  consumer_key char(64) NOT NULL,
  consumer_secret char(43) NOT NULL,
  nonces longtext NULL,
  truncated_key char(7) NOT NULL,
  last_access datetime NULL default null,
  PRIMARY KEY  (key_id),
  KEY consumer_key (consumer_key),
  KEY consumer_secret (consumer_secret)
) $collate
		";

		return $tables;
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		// Customer role
		add_role( 'player', __( 'Player', 'game-portal' ), array(
			'read' => true
		) );

		// Shop manager role
		add_role( 'game_manager', __( 'Game Manager', 'game-portal' ), array(
			'level_9'                => true,
			'level_8'                => true,
			'level_7'                => true,
			'level_6'                => true,
			'level_5'                => true,
			'level_4'                => true,
			'level_3'                => true,
			'level_2'                => true,
			'level_1'                => true,
			'level_0'                => true,
			'read'                   => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true,
			'edit_users'             => true,
			'edit_posts'             => true,
			'edit_pages'             => true,
			'edit_published_posts'   => true,
			'edit_published_pages'   => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_others_posts'      => true,
			'edit_others_pages'      => true,
			'publish_posts'          => true,
			'publish_pages'          => true,
			'delete_posts'           => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'delete_others_posts'    => true,
			'delete_others_pages'    => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'list_users'             => true
		) );

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'game_manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}
	}

	/**
	 * Get capabilities for GamePortal - these are assigned to admin/shop manager during installation or reset.
	 *
	 * @return array
	 */
	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_game_portal'
		);

		$capability_types = array( 'preview', 'level' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms"
			);
		}

		return $capabilities;
	}

	/**
	 * game_portal_remove_roles function.
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$capabilities = self::get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->remove_cap( 'game_manager', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}

		remove_role( 'player' );
		remove_role( 'game_manager' );
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Install files and folders for uploading files and prevent hotlinking
		$upload_dir      = wp_upload_dir();

		$files = array(
			array(
				'base'    => $upload_dir['basedir'] . '/gp-invoices',
				'file'    => 'index.html',
				'content' => ''
			),
			array(
				'base'    => $upload_dir['basedir'] . '/gp-invoices',
				'file'    => '.htaccess',
				'content' => 'deny from all'
			)
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param    mixed $links Plugin Action links
	 *
	 * @return    array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=gp-settings' ) . '" title="' . esc_attr( __( 'View Game Portal Settings', 'game-portal' ) ) . '">' . __( 'Settings', 'game-portal' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables
	 *
	 * @return string[]
	 */
	public static function wpmu_drop_tables( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . 'game_portal_sessions';
		$tables[] = $wpdb->prefix . 'game_portal_api_keys';

		return $tables;
	}

	/**
	 * Get slug from path
	 *
	 * @param  string $key
	 *
	 * @return string
	 */
	private static function format_plugin_slug( $key ) {
		$slug = explode( '/', $key );
		$slug = explode( '.', end( $slug ) );

		return $slug[0];
	}

	/**
	 * Install a plugin from .org in the background via a cron job (used by
	 * installer - opt in).
	 * @param string $plugin_to_install_id
	 * @param array $plugin_to_install
	 * @since 2.6.0
	 */
	public static function background_installer( $plugin_to_install_id, $plugin_to_install ) {
		if ( ! empty( $plugin_to_install['repo-slug'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			WP_Filesystem();

			$skin              = new\ Automatic_Upgrader_Skin;
			$upgrader          = new \WP_Upgrader( $skin );
			$installed_plugins = array_map( array( __CLASS__, 'format_plugin_slug' ), array_keys( get_plugins() ) );
			$plugin_slug       = $plugin_to_install['repo-slug'];
			$plugin            = $plugin_slug . '/' . $plugin_slug . '.php';
			$installed         = false;
			$activate          = false;

			// See if the plugin is installed already
			if ( in_array( $plugin_to_install['repo-slug'], $installed_plugins ) ) {
				$installed = true;
				$activate  = ! is_plugin_active( $plugin );
			}

			// Install this thing!
			if ( ! $installed ) {
				// Suppress feedback
				ob_start();

				try {
					$plugin_information = plugins_api( 'plugin_information', array(
						'slug'   => $plugin_to_install['repo-slug'],
						'fields' => array(
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'homepage'          => false,
							'donate_link'       => false,
							'author_profile'    => false,
							'author'            => false,
						),
					) );

					if ( is_wp_error( $plugin_information ) ) {
						throw new \Exception( $plugin_information->get_error_message() );
					}

					$package  = $plugin_information->download_link;
					$download = $upgrader->download_package( $package );

					if ( is_wp_error( $download ) ) {
						throw new \Exception( $download->get_error_message() );
					}

					$working_dir = $upgrader->unpack_package( $download, true );

					if ( is_wp_error( $working_dir ) ) {
						throw new \Exception( $working_dir->get_error_message() );
					}

					$result = $upgrader->install_package( array(
						'source'                      => $working_dir,
						'destination'                 => WP_PLUGIN_DIR,
						'clear_destination'           => false,
						'abort_if_destination_exists' => false,
						'clear_working'               => true,
						'hook_extra'                  => array(
							'type'   => 'plugin',
							'action' => 'install',
						),
					) );

					if ( is_wp_error( $result ) ) {
						throw new \Exception( $result->get_error_message() );
					}

					$activate = true;

				} catch ( \Exception $e ) {
					GP_Admin_Notices::add_custom_notice(
						$plugin_to_install_id . '_install_error',
						sprintf(
							__( '%1$s could not be installed (%2$s). %3$sPlease install it manually by clicking here.%4$s', 'game-portal' ),
							$plugin_to_install['name'],
							$e->getMessage(),
							'<a href="' . esc_url( admin_url( 'index.php?gp-install-plugin-redirect=' . $plugin_to_install['repo-slug'] ) ) . '">',
							'</a>'
						)
					);
				}

				// Discard feedback
				ob_end_clean();
			}

			wp_clean_plugins_cache();

			// Activate this thing
			if ( $activate ) {
				try {
					$result = activate_plugin( $plugin );

					if ( is_wp_error( $result ) ) {
						throw new \Exception( $result->get_error_message() );
					}

				} catch ( \Exception $e ) {
					GP_Admin_Notices::add_custom_notice(
						$plugin_to_install_id . '_install_error',
						sprintf(
							__( '%1$s was installed but could not be activated. %2$sPlease activate it manually by clicking here.%3$s', 'game-portal' ),
							$plugin_to_install['name'],
							'<a href="' . admin_url( 'plugins.php' ) . '">',
							'</a>'
						)
					);
				}
			}
		}
	}

	/**
	 * Payments Step save.
	 */
	public static function gp_setup_plugins_save() {

		$plugins = self::get_plugins();

		foreach ( $plugins as $plugin_id => $plugin ) {
			// If repo-slug is defined, download and install plugin from .org.
			if ( ! empty( $plugin['repo-slug'] ) ) {
				wp_schedule_single_event( time() + 10, 'game_portal_plugin_background_installer', array( $plugin_id, $plugin ) );
			}
		}
	}

	/**
	 * Simple array of gateways to show in wizard.
	 * @return array
	 */
	protected static function get_plugins() {
		$plugins = array(
			'paypal-cart' => array(
				'name'        => __( 'WordPress Simple Paypal Shopping Cart', 'game-portal' ),
				'description' => __( 'WordPress Simple Paypal Shopping Cart allows you to add an ‘Add to Cart’ button for your product on any posts or pages.', 'game-portal' ),
				'image'       => '',
				'class'       => '',
				'repo-slug'   => 'wordpress-simple-paypal-shopping-cart',
			)
		);

		return $plugins;
	}
}
