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
 * @author   Valentyn Riaboshtan
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
			'qtx_import'           => false,
			'rated'                => false,
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
			wp_send_json_error( __( 'No locale sending', 'wp-multilang' ) );
		}

		foreach ( $options as $language ) {
			if ( $language['translation'] == $locale ) {
				wp_send_json_error( __( 'Localization using', 'wp-multilang' ) );
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

		$files_delete[] = WP_LANG_DIR . '/' . $locale . '.po';
		$files_delete[] = WP_LANG_DIR . '/' . $locale . '.mo';

		foreach ( $files_delete as $file ) {
			wp_delete_file( $file );
		}

		wp_send_json_success( __( 'Localization deleted', 'wp-multilang' ) );
	}

	/**
	 * Set default language for all posts, terms, fields, options
	 */
	public static function set_default_language() {

		check_ajax_referer( 'set-default-language', 'security' );

		global $wpdb;

		$lang       = wpm_get_default_language();
		$post_types = get_post_types( '', 'names' );

		foreach ( $post_types as $post_type ) {

			$post_config = wpm_get_post_config( $post_type );

			if ( null === $post_config ) {
				continue;
			}

			$fields  = wpm_filter_post_config_fields( array_keys( $post_config ) );
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, " . implode( ', ', $fields ) . " FROM {$wpdb->posts} WHERE post_type = '%s';", esc_sql( $post_type ) ) );

			foreach ( $results as $result ) {
				$args       = array();
				$new_result = self::set_default_language_for_object( $result, $post_config );
				foreach ( get_object_vars( $new_result ) as $key => $content ) {
					if ( 'ID' === $key ) {
						continue;
					}

					$args[ $key ] = $content;
				}

				$wpdb->update( $wpdb->posts, $args, array( 'ID' => $result->ID ) );
			}
		}

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			$taxonomy_config = wpm_get_taxonomy_config( $taxonomy );

			if ( null === $taxonomy_config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.term_id, `name`, description FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s';", esc_sql( $taxonomy ) ) );

			foreach ( $results as $result ) {

				$result      = self::set_default_language_for_object( $result, $taxonomy_config );
				$description = $result->description;
				$name        = $result->name;

				$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_id' => $result->term_id ) );
				$wpdb->update( $wpdb->terms, compact( 'name' ), array( 'term_id' => $result->term_id ) );
			}
		}

		$config = wpm_get_config();

		foreach ( $config['post_fields'] as $field => $config ) {

			if ( null === $config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;
				$serialized = false;
				$jsoned     = false;

				if ( is_serialized_string( $meta_value ) ) {
					$serialized = true;
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$jsoned     = true;
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->postmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['term_fields'] as $field => $config ) {

			if ( null === $config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;
				$serialized = false;
				$jsoned     = false;

				if ( is_serialized_string( $meta_value ) ) {
					$serialized = true;
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$jsoned     = true;
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->termmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['comment_fields'] as $field => $config ) {

			if ( null === $config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->commentmeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;
				$serialized = false;
				$jsoned     = false;

				if ( is_serialized_string( $meta_value ) ) {
					$serialized = true;
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$jsoned     = true;
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->commentmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
			}
		}

		foreach ( $config['user_fields'] as $field => $config ) {

			if ( null === $config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '%s';", $field ) );

			foreach ( $results as $result ) {
				$meta_value = $result->meta_value;
				$serialized = false;
				$jsoned     = false;

				if ( is_serialized_string( $meta_value ) ) {
					$serialized = true;
					$meta_value = unserialize( $meta_value );
					$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $meta_value ) ) {
					$jsoned     = true;
					$meta_value = json_decode( $meta_value, true );
					$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
				}

				if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $meta_value ) ) {
					$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->usermeta, compact( 'meta_value' ), array( 'umeta_id' => $result->umeta_id ) );
			}
		}

		foreach ( $config['options'] as $option => $config ) {

			if ( null === $config ) {
				continue;
			}

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_name = '%s';", $option ) );

			foreach ( $results as $result ) {
				$option_value = $result->option_value;
				$serialized = false;
				$jsoned     = false;

				if ( is_serialized_string( $option_value ) ) {
					$serialized = true;
					$option_value = unserialize( $option_value );
					$option_value = serialize( wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang ) );
				}

				if ( json_decode( $option_value ) ) {
					$jsoned       = true;
					$option_value = json_decode( $option_value, true );
					$option_value = wp_json_encode( wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang ) );
				}

				if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $option_value ) ) {
					$option_value = wpm_set_new_value( $option_value, wpm_translate_value( $option_value, $lang ), $config, $lang );
				}

				$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
			}
		}


		if ( isset( $config['site_options'] ) ) {

			foreach ( $config['site_options'] as $option => $config ) {

				if ( null === $config ) {
					continue;
				}

				$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->sitemeta} WHERE meta_key = '%s';", $option ) );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;
					$serialized = false;
					$jsoned     = false;

					if ( is_serialized_string( $meta_value ) ) {
						$serialized = true;
						$meta_value = unserialize( $meta_value );
						$meta_value = serialize( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
					}

					if ( json_decode( $meta_value ) ) {
						$jsoned     = true;
						$meta_value = json_decode( $meta_value, true );
						$meta_value = wp_json_encode( wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang ) );
					}

					if ( ! $jsoned && ! $serialized && ! wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_set_new_value( $meta_value, wpm_translate_value( $meta_value, $lang ), $config, $lang );
					}

					$wpdb->update( $wpdb->sitemeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}
			}
		}

		wp_cache_flush();

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
			if ( ! isset( $object_config[ $key ] ) ) {
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
						$content      = unserialize( $content );
						$object->$key = serialize( wpm_set_new_value( $content, wpm_translate_value( $content, $lang ), $object_config[ $key ], $lang ) );
						break;
					}

					if ( json_decode( $content ) ) {
						$content      = json_decode( $content, true );
						$object->$key = wp_json_encode( wpm_set_new_value( $content, wpm_translate_value( $content, $lang ), $object_config[ $key ], $lang ) );
						break;
					}

					if ( ! wpm_is_ml_string( $content ) ) {
						$object->$key = wpm_set_new_value( $content, wpm_translate_string( $content, $lang ), $object_config[ $key ], $lang );
						break;
					}
			}
		}

		return $object;
	}

	/**
	 * Import translations for terms from qTranslate
	 *
	 * @author   Soft79
	 */
	public static function qtx_import() {

		check_ajax_referer( 'qtx-import', 'security' );

		$term_count = 0;

		if ( $qtranslate_terms = get_option( 'qtranslate_term_name', array() ) ) {

			$taxonomies = get_taxonomies();
			$terms      = get_terms( array('taxonomy' => $taxonomies, 'hide_empty' => false ) );

			foreach ( $terms as $term ) {
				$original = $term->name;

				//Translation available?
				if ( ! isset( $qtranslate_terms[ $original ] ) ) {
					continue;
				}

				//Translate the name
				$strings = wpm_value_to_ml_array( $original );
				foreach ( $qtranslate_terms[ $original ] as $code => $translation ) {
					$strings = wpm_set_language_value( $strings, $translation, array(), $code );
				}

				//Update the name
				$term->name = wpm_ml_value_to_string( $strings );
				if ( $term->name !== $original ) {
					$result = wp_update_term( $term->term_id, $term->taxonomy, array( 'name' => $term->name ) );
					if ( ! is_wp_error( $result ) ) {
						$term_count++;
					}
				}
			}

			delete_option( 'qtranslate_term_name' );
		}

		wp_send_json( sprintf( __( '%d terms were imported successfully.', 'wp-multilang' ), $term_count ) );
	}

	/**
	 * Triggered when clicking the rating footer.
	 */
	public static function rated() {
		if ( ! current_user_can( 'manage_translations' ) ) {
			wp_die( -1 );
		}
		update_option( 'wpm_admin_footer_text_rated', 1 );
		wp_die();
	}
}
