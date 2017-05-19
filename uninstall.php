<?php
/**
 * WP Multilang Uninstall
 *
 * Uninstalling WooCommerce deletes user roles, pages, tables, and options.
 *
 * @author      VaLeXaR
 * @category    Core
 * @package     WPMultilang/Uninstaller
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once __DIR__ . '/lib/autoloader.php';

$uninstall_translations = get_option( 'wpm_uninstall_translations', '' );

if ( $uninstall_translations ) {

	require_once 'core/wpm-language-functions.php';
	require_once 'core/wpm-translation-functions.php';

	$config    = get_option( 'wpm_config' );
	$config    = apply_filters( 'wpm_load_config', $config );
	$languages = wpm_get_languages();
	$lang      = $languages[ wpm_get_default_locale() ];


	foreach ( $config as $key => $item_config ) {

		switch ( $key ) {

			case 'post_types':

				$posts_config = apply_filters( "wpm_posts_config", $item_config );

				foreach ( $posts_config as $post_type => $post_config ) {

					$posts_config = apply_filters( "wpm_posts_{$post_type}_config", $post_config );
					$results      = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_content, post_title, post_excerpt FROM {$wpdb->posts} WHERE post_type = '%s';", $post_type ) );

					foreach ( $results as $result ) {
						$post_content = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->post_content ), $lang ) );
						$post_title   = wpm_translate_string( $result->post_title, $lang );
						$post_excerpt = wpm_translate_string( $result->post_excerpt, $lang );
						$wpdb->update( $wpdb->posts, compact( 'post_content', 'post_title', 'post_excerpt' ), array('ID' => $result->ID) );
					}
				}

				break;

			case 'post_fields' :

				$results     = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->postmeta};" );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->postmeta, compact( 'meta_value' ), array('meta_id' => $result->meta_id) );
				}

				break;

			case 'taxonomies' :

				$taxonomies_config = apply_filters( 'wpm_taxonomies_config', $item_config );

				foreach ($taxonomies_config as $term => $taxonomy_config) {
					$results      = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, description FROM {$wpdb->term_taxonomy} WHERE taxonomy = '%s';", $term ) );

					foreach ( $results as $result ) {
						$description = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->description ), $lang ) );
						$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_id' => $result->term_id ) );
						$name = $wpdb->get_var( $wpdb->prepare( "SELECT `name` FROM {$wpdb->terms} WHERE term_id = '%s';", $result->term_id ) );
						$name = wpm_translate_string( $name, $lang );
						$wpdb->update( $wpdb->terms, compact( 'name' ), array( 'term_id' => $result->term_id ) );
					}
				}

				break;

			case 'term_fields' :

				$results     = $wpdb->get_results( "SELECT meta_id, meta_value FROM {$wpdb->termmeta};" );
				foreach ( $results as $result ) {
					$meta_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->meta_value ), $lang ) );
					$wpdb->update( $wpdb->termmeta, compact( 'meta_value' ), array('meta_id' => $result->meta_id) );
				}

				break;

			case 'options' :

				foreach ($item_config as $option => $option_config ) {
					$result = $wpdb->get_row( $wpdb->prepare( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_name = 'option_%s';", $option ) );
					$option_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->option_value ), $lang ) );
					$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
				}

				break;

			case 'widgets' :

//				foreach ($item_config as $option => $option_config ) {
//					$result = $wpdb->get_row( $wpdb->prepare( "SELECT option_id, option_value FROM {$wpdb->options} WHERE option_name = 'option_%s';", $result->term_id ) );
//					$option_value = maybe_serialize( wpm_translate_value( maybe_unserialize( $result->option_value ), $lang ) );
//					$wpdb->update( $wpdb->options, compact( 'option_value' ), array( 'option_id' => $result->option_id ) );
//				}

				break;
		}

	}

	// Delete options.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpm\_%';" );

	// Clear any cached data that has been removed
	wp_cache_flush();
}
