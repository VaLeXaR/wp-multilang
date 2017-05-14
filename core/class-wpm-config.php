<?php

namespace WPM\Core;

class WPM_Config {

	public $config_files = array();
	public $active_plugins = array();
	static $config = array();

	static function load_config_run() {
		self::load_core_configs();
//		$this->load_plugins_config();
//		$this->load_theme_config();
		self::parse_config_files();
		update_option( 'wpm_config', self::$config );
	}


	public function load_core_configs() {
		$config_path = ( dirname( WPM_PLUGIN_FILE ) . '/configs/' );
		foreach ( glob( $config_path . '*.json' ) as $config_file ) {
			$this->config_files[ pathinfo( $config_file, PATHINFO_DIRNAME ) ] = $config_file;
		}
	}

	public function load_plugins_config() {
		if ( is_multisite() ) {
			// Get multi site plugins
			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $p => $dummy ) {
					if ( ! $this->check_on_config_file( $p ) ) {
						continue;
					}
					$plugin_slug = dirname( $p );
					$config_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
					if ( trim( $plugin_slug, '\/.' ) && file_exists( $config_file ) ) {
						$this->config_files[] = $config_file;
					}
				}
			}
		}

		// Get single site or current blog active plugins
		$plugins = get_option( 'active_plugins' );
		if ( ! empty( $plugins ) ) {
			foreach ( $plugins as $p ) {
				if ( ! $this->check_on_config_file( $p ) ) {
					continue;
				}

				$plugin_slug = dirname( $p );
				$config_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
				if ( trim( $plugin_slug, '\/.' ) && file_exists( $config_file ) ) {
					$this->config_files[] = $config_file;
				}
			}
		}

		// Get the must-use plugins
		$mu_plugins = wp_get_mu_plugins();

		if ( ! empty( $mu_plugins ) ) {
			foreach ( $mu_plugins as $mup ) {
				if ( ! $this->check_on_config_file( $mup ) ) {
					continue;
				}

				$plugin_dir_name  = dirname( $mup );
				$plugin_base_name = basename( $mup, ".php" );
				$plugin_sub_dir   = $plugin_dir_name . '/' . $plugin_base_name;
				if ( file_exists( $plugin_sub_dir . '/wpm-config.json' ) ) {
					$config_file          = $plugin_sub_dir . '/wpm-config.json';
					$this->config_files[] = $config_file;
				}
			}
		}
	}

	public function check_on_config_file( $name ) {

		if ( empty( $this->active_plugins ) ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$this->active_plugins = get_plugins();
		}
		$config_index_file_data = maybe_unserialize( get_option( 'wpm_config_index' ) );
		$config_files_arr       = maybe_unserialize( get_option( 'wpm_config_files_arr' ) );

		if ( ! $config_index_file_data || ! $config_files_arr ) {
			return true;
		}

		if ( isset( $this->active_plugins[ $name ] ) ) {
			$plugin_info      = $this->active_plugins[ $name ];
			$plugin_slug      = dirname( $name );
			$name             = $plugin_info['Name'];
			$config_data      = $config_index_file_data->plugins;
			$config_files_arr = $config_files_arr->plugins;
			$config_file      = WP_PLUGIN_DIR . '/' . $plugin_slug . '/wpm-config.json';
			$type             = 'plugin';
		} else {
			$config_data      = $config_index_file_data->themes;
			$config_files_arr = $config_files_arr->themes;
			$config_file      = get_template_directory() . '/wpm-config.json';
			$type             = 'theme';
		}

		foreach ( $config_data as $item ) {
			if ( $name == $item->name && isset( $config_files_arr[ $item->name ] ) ) {
				if ( $item->override_local || ! file_exists( $config_file ) ) {
					end( $this->config_files );
					$key                                            = key( $this->config_files ) + 1;
					$this->config_files[ $key ]                     = new \stdClass();
					$this->config_files[ $key ]->config             = json_decode( $config_files_arr[ $item->name ], true );
					$this->config_files[ $key ]->type               = $type;
					$this->config_files[ $key ]->admin_text_context = basename( dirname( $config_file ) );

					return false;
				} else {
					return true;
				}
			}
		}

		return true;

	}

	public function load_theme_config() {
		$theme_data = wp_get_theme();
		if ( ! $this->check_on_config_file( $theme_data->get( 'Name' ) ) ) {
			return $this->config_files;
		}

		$parent_theme = $theme_data->parent_theme;
		if ( $parent_theme && ! $this->check_on_config_file( $parent_theme ) ) {
			return $this->config_files;
		}

		if ( get_template_directory() != get_stylesheet_directory() ) {
			$config_file = get_stylesheet_directory() . '/wpm-config.json';
			if ( file_exists( $config_file ) ) {
				$this->config_files[] = $config_file;
			}
		}

		$config_file = get_template_directory() . '/wpm-config.json';
		if ( file_exists( $config_file ) ) {
			$this->config_files[] = $config_file;
		}
	}

	public function get_theme_config_file() {
		if ( get_template_directory() != get_stylesheet_directory() ) {
			$config_file = get_stylesheet_directory() . '/wpm-config.json';
			if ( file_exists( $config_file ) ) {
				return $config_file;
			}
		}

		$config_file = get_template_directory() . '/wpm-config.json';
		if ( file_exists( $config_file ) ) {
			return $config_file;
		}

		return false;
	}

	public function parse_config_files() {
		if ( ! empty( $this->config_files ) ) {
			foreach ( $this->config_files as $file ) {
				$config       = is_object( $file ) ? $file->config : json_decode( file_get_contents( $file ), true );
				self::$config = array_merge_recursive( self::$config, $config );
			}
		}
	}


	public function get_active_plugins() {
		$active_plugin_names = array();
		foreach ( get_plugins() as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_plugin_names[ pathinfo( $plugin_file, PATHINFO_DIRNAME ) ] = $plugin_data;
			}
		}

		return $active_plugin_names;
	}
}
