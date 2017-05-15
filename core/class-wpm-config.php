<?php

namespace WPM\Core;

class WPM_Config {

	static $config_files = array();
	static $active_plugins = array();
	static $config = array();

	static public function load_config_run() {
		self::load_core_configs();
		self::load_plugins_config();
		self::load_theme_config();
		self::parse_config_files();
		update_option( 'wpm_config', self::$config );
	}


	static public function load_core_configs() {
		$config_path = ( dirname( WPM_PLUGIN_FILE ) . '/configs/' );
		foreach ( glob( $config_path . '*.json' ) as $config_file ) {
			self::$config_files[ pathinfo( $config_file, PATHINFO_FILENAME ) ] = $config_file;
		}

		return self::$config_files;
	}

	static public function load_plugins_config() {

		$plugins = get_option( 'active_plugins' );
		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $p ) {
				$plugin_slug = dirname( $p );
				$config_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
				if ( trim( $plugin_slug, '\/.' ) && file_exists( $config_file ) ) {
					self::$config_files[ $plugin_slug ] = $config_file;
				}
			}
		}

		$mu_plugins = wp_get_mu_plugins();

		if ( ! empty( $mu_plugins ) ) {
			foreach ( $mu_plugins as $mup ) {
				$plugin_dir_name  = dirname( $mup );
				$plugin_base_name = basename( $mup, ".php" );
				$plugin_sub_dir   = $plugin_dir_name . '/' . $plugin_base_name;
				if ( file_exists( $plugin_sub_dir . '/wpm-config.json' ) ) {
					$config_file          = $plugin_sub_dir . '/wpm-config.json';
					self::$config_files[ $plugin_base_name ] = $config_file;
				}
			}
		}
	}

	static public function load_theme_config() {

		$config_file = get_template_directory() . '/wpm-config.json';
		if ( file_exists( $config_file ) ) {
			self::$config_files[] = $config_file;
		}

		if ( get_template_directory() != get_stylesheet_directory() ) {
			$config_file = get_stylesheet_directory() . '/wpm-config.json';
			if ( file_exists( $config_file ) ) {
				self::$config_files[] = $config_file;
			}
		}
	}

	static public function parse_config_files() {
		if ( ! empty( self::$config_files ) ) {
			foreach ( self::$config_files as $file ) {
				$config       = is_object( $file ) ? $file->config : json_decode( file_get_contents( $file ), true );
				self::$config = wpm_array_merge_recursive( self::$config, $config );
			}
		}
	}
}
