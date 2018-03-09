<?php
/**
 * WP Multilang Uninstall
 *
 * Uninstalling  WP Multilang deletes translations and options.
 *
 * @author   Valentyn Riaboshtan
 * @category    Core
 * @package     WPM/Uninstaller
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once __DIR__ . '/wp-multilang.php';

if ( get_option( 'wpm_uninstall_translations', 'no' ) === 'yes' ) {

	// Roles + caps.
	WPM\Includes\WPM_Install::remove_roles();
	$config           = wpm_get_config();
	$default_language = wpm_get_default_language();

	$post_types = get_post_types( '', 'names' );

	foreach ( $post_types as $post_type ) {

		$post_config = wpm_get_post_config( $post_type );

		if ( is_null( $post_config ) ) {
			continue;
		}

		$fields  = wpm_filter_post_config_fields( array_keys( $post_config ) );
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, " . implode( ', ', $fields ) . " FROM {$wpdb->posts} WHERE post_type = '%s';", esc_sql( $post_type ) ) );

		foreach ( $results as $result ) {

			$args       = array();
			$new_result = wpm_translate_object( $result, $default_language );

			foreach ( get_object_vars( $new_result ) as $key => $content ) {
				if ( 'ID' == $key ) {
					continue;
				}

				$args[ $key ] = $content;
			}

			$wpdb->update( $wpdb->posts, $args, array( 'ID' => $result->ID ) );
		}
	}

	$taxonomies = get_taxonomies();

	foreach ( $taxonomies as $taxonomy ) {

		if ( is_null( wpm_get_taxonomy_config( $taxonomy ) ) ) {
			continue;
		}

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.term_id, `name`, description FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s';", esc_sql( $taxonomy ) ) );

		foreach ( $results as $result ) {

			$result      = wpm_translate_object( $result, $default_language );
			$description = $result->description;
			$name        = $result->name;

			$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_id' => $result->term_id ) );
			$wpdb->update( $wpdb->terms, compact( 'name' ), array( 'term_id' => $result->term_id ) );
		}
	}

	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_%';" );

	foreach ( $config as $key => $item_config ) {

		switch ( $key ) {

			case 'post_fields':
				$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_languages' ) );

				$results = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%![:__!]%' ESCAPE '!' OR meta_value LIKE '%{:__}%' OR meta_value LIKE '%<!--:__-->%';" );
				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = serialize( wpm_translate_value( unserialize( $meta_value ), $default_language ) );
					}

					if ( isJSON( $meta_value ) ) {
						$meta_value = wp_json_encode( wpm_translate_value( json_decode( $meta_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_translate_string( $meta_value, $default_language );
					}

					$wpdb->update( $wpdb->postmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'term_fields':
				$wpdb->delete( $wpdb->termmeta, array( 'meta_key' => '_languages' ) );

				$results = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_value LIKE '%![:__!]%' ESCAPE '!' OR meta_value LIKE '%{:__}%' OR meta_value LIKE '%<!--:__-->%';" );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = serialize( wpm_translate_value( unserialize( $meta_value ), $default_language ) );
					}

					if ( isJSON( $meta_value ) ) {
						$meta_value = wp_json_encode( wpm_translate_value( json_decode( $meta_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_translate_string( $meta_value, $default_language );
					}

					$wpdb->update( $wpdb->termmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'comment_fields':
				$wpdb->delete( $wpdb->commentmeta, array( 'meta_key' => '_languages' ) );

				$results = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->commentmeta} WHERE meta_value LIKE '%s' OR meta_value LIKE '%![:__!]%' ESCAPE '!' OR meta_value LIKE '%{:__}%' OR meta_value LIKE '%<!--:__-->%';" );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = serialize( wpm_translate_value( unserialize( $meta_value ), $default_language ) );
					}

					if ( isJSON( $meta_value ) ) {
						$meta_value = wp_json_encode( wpm_translate_value( json_decode( $meta_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_translate_string( $meta_value, $default_language );
					}

					$wpdb->update( $wpdb->commentmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'user_fields':

				$results = $wpdb->get_results( "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_value LIKE '%![:__!]%' ESCAPE '!' OR meta_value LIKE '%{:__}%' OR meta_value LIKE '%<!--:__-->%';" );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = serialize( wpm_translate_value( unserialize( $meta_value ), $default_language ) );
					}

					if ( isJSON( $meta_value ) ) {
						$meta_value = wp_json_encode( wpm_translate_value( json_decode( $meta_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_translate_string( $meta_value, $default_language );
					}

					$wpdb->update( $wpdb->usermeta, compact( 'meta_value' ), array( 'umeta_id' => $result->umeta_id ) );
				}

				break;

			case 'options':

				$results = $wpdb->get_results( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_value LIKE '%![:__!]%' ESCAPE '!' OR option_value LIKE '%{:__}%' OR option_value LIKE '%<!--:__-->%';" );

				foreach ( $results as $result ) {
					$option_value = $result->option_value;

					if ( is_serialized_string( $option_value ) ) {
						$option_value = serialize( wpm_translate_value( unserialize( $option_value ), $default_language ) );
					}

					if ( isJSON( $option_value ) ) {
						$option_value = wp_json_encode( wpm_translate_value( json_decode( $option_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $option_value ) ) {
						$option_value = wpm_translate_string( $option_value, $default_language );
					}

					$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
				}

				break;

			case 'site_options':

				$results = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->sitemeta} WHERE meta_value LIKE '%![:__!]%' ESCAPE '!' OR meta_value LIKE '%{:__}%' OR meta_value LIKE '%<!--:__-->%';" );

				foreach ( $results as $result ) {
					$meta_value = $result->meta_value;

					if ( is_serialized_string( $meta_value ) ) {
						$meta_value = serialize( wpm_translate_value( unserialize( $meta_value ), $default_language ) );
					}

					if ( isJSON( $meta_value ) ) {
						$meta_value = wp_json_encode( wpm_translate_value( json_decode( $meta_value, true ), $default_language ) );
					}

					if ( wpm_is_ml_string( $meta_value ) ) {
						$meta_value = wpm_translate_string( $meta_value, $default_language );
					}

					$wpdb->update( $wpdb->sitemeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;
		} // End switch().
	} // End foreach().

	// Clear any cached data that has been removed
	wp_cache_flush();
} // End if().

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpm_%';" );
