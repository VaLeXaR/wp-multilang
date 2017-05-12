<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Posts extends \QtN_Object {

	public $object_type = 'post';
	public $object_table = 'postmeta';

	public function __construct() {
		add_filter( 'the_posts', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'the_post', 'qtn_translate_object', 0 );
		add_filter( 'the_title', 'qtn_translate_string', 0);
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );
//		add_filter( 'heartbeat_received', array( 'WPGlobus_Filters', 'filter__heartbeat_received' ), 501, 2 );

//		add_filter( 'get_pages', array( $this, 'filter_posts' ), 0, 2 );
//		add_filter( 'the_content', 'qtn_translate_string', 0);
//		add_filter( 'get_the_excerpt', 'qtn_translate_string', 0);
//		add_filter( 'single_post_title', 'qtn_translate_string', 0);
//		add_filter( 'post_type_archive_title', 'qtn_translate_string', 0);
//		add_filter( 'sanitize_title', 'qtn_translate_string', 0);
	}


	public function filter_posts( $posts ) {
		return array_map( 'qtn_translate_object', $posts );
	}


	public static function filter__heartbeat_received( $response, $data ) {

		if ( ! empty( $data['wp_autosave'] ) ) {

			if ( empty( $data['wp_autosave']['post_id'] ) || (int) $data['wp_autosave']['post_id'] == 0 ) {
				/**
				 * wp_autosave may come from edit.php page
				 */
				return $response;
			}

			if ( empty( $data['wpglobus_heartbeat'] ) ) {
				/**
				 * Check for wpglobus key
				 */
				return $response;
			}

			$title_wrap     = false;
			$content_wrap   = false;
			$post_title_ext = '';
			$content_ext    = '';

			foreach ( WPGlobus::Config()->enabled_languages as $language ) {
				if ( $language == WPGlobus::Config()->default_language ) {

					$post_title_ext .= WPGlobus::add_locale_marks( $data['wp_autosave']['post_title'], $language );
					$content_ext .= WPGlobus::add_locale_marks( $data['wp_autosave']['content'], $language );

				} else {

					if ( ! empty( $data['wp_autosave'][ 'post_title_' . $language ] ) ) {
						$title_wrap = true;
						$post_title_ext .= WPGlobus::add_locale_marks( $data['wp_autosave'][ 'post_title_' . $language ], $language );
					}

					if ( ! empty( $data['wp_autosave'][ 'content_' . $language ] ) ) {
						$content_wrap = true;
						$content_ext .= WPGlobus::add_locale_marks( $data['wp_autosave'][ 'content_' . $language ], $language );
					}

				}
			}

			if ( $title_wrap ) {
				$data['wp_autosave']['post_title'] = $post_title_ext;
			}

			if ( $content_wrap ) {
				$data['wp_autosave']['content'] = $content_ext;
			}

			/**
			 * Filter before autosave
			 * @since 1.0.2
			 *
			 * @param array $data ['wp_autosave'] Array of post data.
			 */
			$data['wp_autosave'] = apply_filters( 'wpglobus_autosave_post_data', $data['wp_autosave'] );

			$saved = wp_autosave( $data['wp_autosave'] );

			if ( is_wp_error( $saved ) ) {
				$response['wp_autosave'] = array( 'success' => false, 'message' => $saved->get_error_message() );
			} elseif ( empty( $saved ) ) {
				$response['wp_autosave'] = array( 'success' => false, 'message' => __( 'Error while saving.' ) );
			} else {
				/* translators: draft saved date format, see http://php.net/date */
				$draft_saved_date_format = __( 'g:i:s a' );
				/* translators: %s: date and time */
				$response['wp_autosave'] = array(
					'success' => true,
					'message' => sprintf( __( 'Draft saved at %s.' ), date_i18n( $draft_saved_date_format ) )
				);
			}

		}

		return $response;
	}
}
