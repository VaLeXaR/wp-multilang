<?php

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set configs
 *
 * Class WPM_Config
 *
 * @package  WPM/Includes
 * @category Class
 * @author   Valentyn Riaboshtan
 */
class WPM_Config {

	static $config_files = array();
	static $active_plugins = array();
	static $config = array();

	/**
	 * Run parsing configs
	 */
	public static function load_config_run() {
		self::load_plugins_config();
		self::load_core_configs();
		self::load_theme_config();
		self::parse_config_files();
		wp_cache_set( 'active_plugins', self::$active_plugins, 'wpm' );
		wp_cache_set( 'config', self::$config, 'wpm' );
	}

	/**
	 * Load configs from plugins
	 */
	public static function load_plugins_config() {
		self::$config_files[] = WPM_ABSPATH . 'core-config.json';

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( get_plugins() as $pf => $pd ) {
			if ( is_plugin_active( $pf ) ) {
				$plugin_slug            = dirname( $pf );
				self::$active_plugins[] = $plugin_slug;
				$config_file            = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
				if ( trim( $plugin_slug, '\/.' ) && file_exists( $config_file ) ) {
					self::$config_files[ $plugin_slug ] = $config_file;
				}
			}
		}

		foreach ( wp_get_mu_plugins() as $mup ) {
			$plugin_dir_name        = dirname( $mup );
			$plugin_base_name       = basename( $mup, '.php' );
			self::$active_plugins[] = $plugin_base_name;
			$plugin_sub_dir         = $plugin_dir_name . '/' . $plugin_base_name;
			if ( file_exists( $plugin_sub_dir . '/wpm-config.json' ) ) {
				$config_file                             = $plugin_sub_dir . '/wpm-config.json';
				self::$config_files[ $plugin_base_name ] = $config_file;
			}
		}
	}

	/**
	 * Load configs from WPM
	 */
	public static function load_core_configs() {
		$plugins_config_path = WPM_ABSPATH . 'configs/plugins/';
		foreach ( glob( $plugins_config_path . '*.json' ) as $config_file ) {
			$config_name = pathinfo( $config_file, PATHINFO_FILENAME );
			if ( ! isset( $config_files[ $config_name ] ) && in_array( $config_name, self::$active_plugins, true ) ) {
				self::$config_files[ $config_name ] = $config_file;
			}
		}

		$theme_name        = get_template();
		$theme_config_file = WPM_ABSPATH . 'configs/themes/' . $theme_name . '.json';
		if ( file_exists( $theme_config_file ) ) {
			self::$config_files[ 'theme_' . $theme_name ] = $theme_config_file;
		}
	}

	/**
	 * Load configs from current theme
	 */
	public static function load_theme_config() {

		$config_file = get_template_directory() . '/wpm-config.json';
		if ( file_exists( $config_file ) ) {
			self::$config_files[ 'theme_' . get_template() ] = $config_file;
		}

		if ( get_template_directory() !== get_stylesheet_directory() ) {
			$config_file = get_stylesheet_directory() . '/wpm-config.json';
			if ( file_exists( $config_file ) ) {
				self::$config_files[ 'theme_' . get_stylesheet() ] = $config_file;
			}
		}
	}

	/**
	 * Parsing config files to config array
	 */
	public static function parse_config_files() {

		$config_files = apply_filters( 'wpm_json_files', self::$config_files );

		foreach ( $config_files as $name => $file ) {

			$file = apply_filters( "wpm_{$name}_json_file", $file );

			if ( $file && is_readable( $file ) ) {
				$config = json_decode( file_get_contents( $file ), true );

				if ( is_array( $config ) && ! empty( $config ) ) {
					self::$config = wpm_array_merge_recursive( self::$config, $config );
				}
			}
		}
	}
}
