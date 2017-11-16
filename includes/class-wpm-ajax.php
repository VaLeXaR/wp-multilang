<?php

namespace WPM\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP_Multilang WPM_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    WPM_AJAX
 * @package  WPM/Classes
 * @category Class
 */
class WPM_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_wpm_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Get WPM Ajax Endpoint.
	 *
	 * @param  string $request Optional
	 *
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		return esc_url_raw( add_query_arg( 'wpm-ajax', $request ) );
	}

	/**
	 * Set WPM AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['wpm-ajax'] ) ) {
			if ( ! wp_doing_ajax() ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'WPM_DOING_AJAX' ) ) {
				define( 'WPM_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for WPM Ajax Requests
	 */
	private static function wpm_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Check for WPM Ajax request and fire action.
	 */
	public static function do_wpm_ajax() {
		global $wp_query;

		if ( ! empty( $_GET['wpm-ajax'] ) ) {
			$wp_query->set( 'wpm-ajax', sanitize_text_field( $_GET['wpm-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'wpm-ajax' ) ) {
			self::wpm_ajax_headers();
			do_action( 'wpm_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'delete_lang'          => false,
			'delete_localization'  => false,
			'set_default_language' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wpm_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wpm_' . $ajax_event, array( __CLASS__, $ajax_event ) );

				// GP AJAX can be used for frontend ajax requests
				add_action( 'wpm_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	/**
	 * Remove installed language files and option
	 */
	public static function delete_lang() {

		check_ajax_referer( 'delete-lang', 'security' );

		$language = wpm_get_post_data_by_key( 'language' );
		$options  = wpm_get_lang_option();

		if ( ! $language || ! isset( $options[ $language ] ) || ( wpm_get_user_language() === $language ) || ( wpm_get_default_language() === $language ) ) {
			return;
		}

		unset( $options[ $language ] );

		global $wpdb;
		$wpdb->update( $wpdb->options, array( 'option_value' => maybe_serialize( $options ) ), array( 'option_name' => 'wpm_languages' ) );

		die();
	}

	/**
	 * Remove installed language files and option
	 */
	public static function delete_localization() {

		check_ajax_referer( 'delete-localization', 'security' );

		$locale  = wpm_get_post_data_by_key( 'locale' );
		$options = wpm_get_lang_option();

		if ( ! $locale ) {
			return;
		}

		foreach ( $options as $lang => $language ) {
			if ( $language['translation'] == $locale ) {
				return;
			}
		}

		$files_delete                  = array();
		$installed_plugin_translations = wp_get_installed_translations( 'plugins' );

		foreach ( $installed_plugin_translations as $plugin => $translation ) {
			if ( isset( $translation[ $locale ] ) ) {
				$files_delete[] = WP_LANG_DIR . '/plugins/' . $plugin . '-' . $locale . '.mo';
				$files_delete[] = WP_LANG_DIR . '/plugins/' . $plugin . '-' . $locale . '.po';
			}
		}

		$installed_themes_translations = wp_get_installed_translations( 'themes' );

		foreach ( $installed_themes_translations as $theme => $translation ) {
			if ( isset( $translation[ $locale ] ) ) {
				$files_delete[] = WP_LANG_DIR . '/themes/' . $theme . '-' . $locale . '.mo';
				$files_delete[] = WP_LANG_DIR . '/themes/' . $theme . '-' . $locale . '.po';
			}
		}

		$installed_core_translations = wp_get_installed_translations( 'core' );

		foreach ( $installed_core_translations as $wp_file => $translation ) {
			if ( isset( $translation[ $locale ] ) ) {
				$files_delete[] = WP_LANG_DIR . '/' . $wp_file . '-' . $locale . '.mo';
				$files_delete[] = WP_LANG_DIR . '/' . $wp_file . '-' . $locale . '.po';
			}
		}

		if ( file_exists( WP_LANG_DIR . '/' . $locale . '.mo' ) ) {
			$files_delete[] = WP_LANG_DIR . '/' . $locale . '.mo';
		}

		if ( file_exists( WP_LANG_DIR . '/' . $locale . '.po' ) ) {
			$files_delete[] = WP_LANG_DIR . '/' . $locale . '.po';
		}

		foreach ( $files_delete as $file ) {
			wp_delete_file( $file );
		}

		die();
	}

	/**
	 * Set default language for all posts, terms, fields, options
	 */
	public static function set_default_language() {

		check_ajax_referer( 'set-default-language', 'security' );

		$post_types = get_post_types( '', 'names' );

		foreach ( $post_types as $post_type ) {

			$post_config = wpm_get_post_config( $post_type );

			if ( is_null( $post_config ) ) {
				continue;
			}

			$posts = get_posts( array( 'numberposts' => -1, 'post_type' => $post_type ) );

			foreach ( $posts as $post ) {
				wp_update_post( self::set_default_language_for_object( $post, $post_config ) );
			}
		}

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			$taxonomy_config = wpm_get_taxonomy_config( $taxonomy );

			if ( is_null( $taxonomy_config ) ) {
				continue;
			}

			remove_filter( 'get_term', 'wpm_translate_term', 5 );
			$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
			add_filter( 'get_term', 'wpm_translate_term', 5, 2 );

			foreach ( $terms as $term ) {
				$new_args = get_object_vars( self::set_default_language_for_object( $term, $taxonomy_config ) );
				wp_update_term( $term->term_id, $taxonomy, $new_args );
			}
		}

		global $wpdb;

		$config = wpm_get_config();
		$lang   = wpm_get_default_language();

		foreach ( $config['post_fields'] as $field => $config ) {

			if ( is_null( $config ) ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;

				if ( is_serialized_string( $meta_value ) ) {
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->postmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['term_fields'] as $field => $config ) {

			if ( is_null( $config ) ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;

				if ( is_serialized_string( $meta_value ) ) {
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->termmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['comment_fields'] as $field => $config ) {

			if ( is_null( $config ) ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->commentmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;

				if ( is_serialized_string( $meta_value ) ) {
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->commentmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['user_fields'] as $field => $config ) {

			if ( is_null( $config ) ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;

				if ( is_serialized_string( $meta_value ) ) {
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->usermeta, compact( 'meta_value' ), array( 'umeta_id' => $result->umeta_id ) );
			}
		}

		foreach ( $config['options'] as $option => $config ) {

			if ( is_null( $config ) ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_name = '%s';", $option ) );

			foreach ( $results as $result ) {
				$option_value = $result->option_value;

				if ( is_serialized_string( $option_value ) ) {
					$option_value = unserialize( $option_value );
					$option_value = serialize( wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $option_value ) ) {
					$option_value = json_decode( $option_value, true );
					$option_value = wp_json_encode( wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang ) );
				}

				if ( wpm_is_ml_string( $option_value ) ) {
					$option_value = wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
			}
		}


		if ( isset( $config['site_options'] ) ) {

			foreach ( $config['site_options'] as $option => $config ) {

				if ( is_null( $config ) ) {
					continue;
				}

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->sitemeta} WHERE meta_key = '%s';", $option ) );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = unserialize( $meta_value );
						$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
					}

					if ( json_decode( $meta_value ) ) {
						$meta_value = json_decode( $meta_value, true );
						$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
					}

					$wpdb->update( $wpdb->sitemeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}
			}
		}

		wp_send_json( __( 'Update finished', 'wp-multilang' ) );
	}

	/**
	 * Set default language for object
	 *
	 * @param $object
	 * @param $object_config
	 *
	 * @return mixed
	 */
	private static function set_default_language_for_object( $object, $object_config ) {

		$lang = wpm_get_default_language();

		foreach ( get_object_vars( $object ) as $key => $content ) {
			if ( ! isset( $post_config[ $key ] ) || is_null( $object_config[ $key ] ) ) {
				continue;
			}

			switch ( $key ) {
				case 'attr_title':
				case 'post_title':
				case 'name':
				case 'title':
					$object->$key = wpm_set_new_value( $content, wpm_translate_string( $content, $lang ), $object_config[ $key ], $lang );
					break;
				case 'post_excerpt':
				case 'description':
				case 'post_content':
					if ( is_serialized_string( $content ) ) {
						$content    = unserialize( $content );
						$object->$key = serialize( wpm_set_new_value( $content, wpm_translate_value( $content, $lang ), $object_config[ $key ], $lang ) );
						break;
					}

					if ( json_decode( $content ) ) {
						$content    = json_decode( $content, true );
						$object->$key = wp_json_encode( wpm_set_new_value( $content, wpm_translate_value( $content, $lang ), $object_config[ $key ], $lang ) );
						break;
					}

					if ( wpm_is_ml_string( $content ) ) {
						$object->$key = wpm_set_new_value( $content, wpm_translate_string( $content, $lang ), $object_config[ $key ], $lang );
						break;
					}
			}
		}

		return $object;
	}
}
