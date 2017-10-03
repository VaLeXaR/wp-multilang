<?php
/**
 * WP Multilang Uninstall
 *
 * Uninstalling  WP Multilang deletes translations and options.
 *
 * @author      VaLeXaR
 * @category    Core
 * @package     WPMultilang/Uninstaller
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once __DIR__ . '/lib/autoload.php';

$uninstall_translations = get_option( 'wpm_uninstall_translations', '' );

if ( $uninstall_translations ) {

	require_once 'core/wpm-core-functions.php';

	if ( ! defined( 'WPM_PLUGIN_FILE' ) ) {
		define( 'WPM_PLUGIN_FILE', __DIR__ . '/wp-multilang.php' );
	}

	$config    = wpm_get_config();
	$languages = wpm_get_languages();
	$lang      = $languages[ wpm_get_default_locale() ];

	foreach ( $config as $key => $item_config ) {

		switch ( $key ) {

			case 'post_types':

				foreach ( $item_config as $post_type => $post_config ) {

					if ( is_null( $post_config ) ) {
						continue;
					}

					$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content, post_title, post_excerpt FROM {$wpdb->posts} WHERE post_type = '%s';", esc_sql( $post_type ) ) );

					foreach ( $results as $result ) {
						$post_title   = wpm_translate_string( $result->post_title, $lang );
						$post_excerpt = wpm_translate_string( $result->post_excerpt, $lang );
						$post_content = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->post_content ), $lang ) );
						$wpdb->update( $wpdb->posts, compact( 'post_content', 'post_title', 'post_excerpt' ), array( 'ID' => $result->ID ) );
					}
				}

				break;

			case 'post_fields' :

				$like    = '%' . $wpdb->esc_like( esc_sql( 's:' . strlen( $lang ) . ':"' . $lang . '";' ) ) . '%';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE '%s';", $like ) );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->postmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'taxonomies' :

				foreach ( $item_config as $term => $taxonomy_config ) {

					if ( is_null( $taxonomy_config ) ) {
						continue;
					}

					$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.term_id, `name`, description FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s';", esc_sql( $term ) ) );

					foreach ( $results as $result ) {
						$description = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->description ), $lang ) );
						$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_id' => $result->term_id ) );
						$name = wpm_translate_string( $result->name, $lang );
						$wpdb->update( $wpdb->terms, compact( 'name' ), array( 'term_id' => $result->term_id ) );
					}
				}

				break;

			case 'term_fields' :

				$like    = '%' . $wpdb->esc_like( esc_sql( 's:' . strlen( $lang ) . ':"' . $lang . '";' ) ) . '%';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_value LIKE '%s';", $like ) );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->termmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'comment_fields' :

				$like    = '%' . $wpdb->esc_like( esc_sql( 's:' . strlen( $lang ) . ':"' . $lang . '";' ) ) . '%';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id, meta_value FROM {$wpdb->commentmeta} WHERE meta_value LIKE '%s';", $like ) );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->commentmeta, compact( 'meta_value' ), array( 'meta_id' => $result->meta_id ) );
				}

				break;

			case 'user_fields' :

				$like    = '%' . $wpdb->esc_like( esc_sql( 's:' . strlen( $lang ) . ':"' . $lang . '";' ) ) . '%';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT umeta_id, meta_value FROM {$wpdb->usermeta} WHERE meta_value LIKE '%s';", $like ) );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->usermeta, compact( 'meta_value' ), array( 'umeta_id' => $result->umeta_id ) );
				}

				break;

			case 'options' :

				$like    = '%' . $wpdb->esc_like( esc_sql( 's:' . strlen( $lang ) . ':"' . $lang . '";' ) ) . '%';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_value LIKE '%s';", $like ) );
				foreach ( $results as $result ) {
					$option_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->option_value ), $lang ) );
					$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
				}

				break;
		} // End switch().
	} // End foreach().

	// Clear any cached data that has been removed
	wp_cache_flush();
} // End if().

$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpm\_%';" );
