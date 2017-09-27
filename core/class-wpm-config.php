<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Set configs
 *
 * Class WPM_Config
 * @package  WPM\Core
 * @category Class
 * @author   VaLeXaR
 */
class WPM_Config {

	static $config_files = array();
	static $active_plugins = array();
	static $config = array();

	/**
	 * Run parsing configs
	 */
	static public function load_config_run() {
		self::load_plugins_config();
		self::load_core_configs();
		self::parse_config_files();
		update_option( 'wpm_config', self::$config );
	}

	/**
	 * Load configs from plugins
	 */
	static public function load_plugins_config() {
		self::$config_files[] = dirname( WPM_PLUGIN_FILE ) . '/core-config.json';

		$plugins = get_option( 'active_plugins' );
		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $p ) {
				$plugin_slug            = dirname( $p );
				self::$active_plugins[] = $plugin_slug;
				$config_file            = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
				if ( trim( $plugin_slug, '\/.' ) && file_exists( $config_file ) ) {
					self::$config_files[ $plugin_slug ] = $config_file;
				}
			}
		}

		$mu_plugins = wp_get_mu_plugins();

		if ( ! empty( $mu_plugins ) ) {
			foreach ( $mu_plugins as $mup ) {
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
	}

	/**
	 * Load configs from WPM
	 */
	static public function load_core_configs() {
		$config_path = dirname( WPM_PLUGIN_FILE ) . '/configs/';
		foreach ( glob( $config_path . '*.json' ) as $config_file ) {
			$config_name = pathinfo( $config_file, PATHINFO_FILENAME );
			if ( in_array( $config_name, self::$active_plugins, true ) && ! isset( $config_files[ $config_name ] ) ) {
				self::$config_files[ $config_name ] = $config_file;
			}
		}
	}

	/**
	 * Parsing config files to config array
	 */
	static public function parse_config_files() {

		foreach ( self::$config_files as $file ) {
			if ( $file && is_readable( $file ) ) {
				$config = json_decode( file_get_contents( $file ), true );

				if ( is_array( $config ) && ! empty( $config ) ) {
					self::$config = wpm_array_merge_recursive( self::$config, $config );
				}
			}
		}
	}

	/**
	 * Load configs from current theme
	 */
	static public function load_theme_config() {

		$config_file = get_template_directory() . '/wpm-config.json';
		if ( file_exists( $config_file ) ) {
			self::$config_files[] = $config_file;
		}

		if ( get_template_directory() !== get_stylesheet_directory() ) {
			$config_file = get_stylesheet_directory() . '/wpm-config.json';
			if ( file_exists( $config_file ) ) {
				self::$config_files[] = $config_file;
			}
		}

		self::parse_config_files();

		return self::$config;
	}
}
