<?php

namespace QtNext\Core;

class QtN_Posts extends \QtN_Object {

	public $object_type = 'post';
	public $object_table = 'postmeta';

	public function __construct() {
		add_filter( 'the_posts', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'get_pages', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'the_post', array( $this, 'filter_post' ), 0, 2 );
		add_filter( 'get_edit_post_link', array( $this, 'edit_post_link' ), 0, 3 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );
		add_action( 'wp_insert_post_data', array( $this, 'save_post' ), 0, 2 );
		add_action( 'wp_insert_attachment_data', array( $this, 'save_post' ), 0, 2 );
	}

	public function filter_posts( $posts ) {
		return array_map( 'qtn_translate_object', $posts );
	}

	public function filter_post( $post ) {
		return qtn_translate_object( $post );
	}

	public function translate_post_field( $value, $post_id, $context ) {

		if ( 'display' == $context ) {
			$value = qtn_translate_value( $value );
		}

		return $value;
	}

	public function edit_post_link( $link, $post_id, $context ) {
		global $qtn_config;
		if ( 'display' == $context && in_array( get_post_type( $post_id ), $qtn_config->settings['post_types'] ) ) {
			$link = add_query_arg( 'edit_lang', $qtn_config->languages[ get_locale() ], $link );
		}

		return $link;
	}

	public function save_post( $data, $postarr ) {
		global $qtn_config;

		if ( ! in_array( $data['post_type'], $qtn_config->settings['post_types'] ) ) {
			return $data;
		}

		if ( 'attachment' !== $data['post_type'] ) {

			if ( 'trash' == $postarr['post_status'] ) {
				return $data;
			}

			if ( isset( $_GET['action'] ) && 'untrash' == $_GET['action'] ) {
				return $data;
			}
		}

		$post_id = isset( $data['ID'] ) ? qtn_clean( $data['ID'] ) : ( isset( $postarr['ID'] ) ? qtn_clean( $postarr['ID'] ) : 0 );

		if ( ! $post_id ) {
			return $data;
		}

		foreach ( $data as $key => $content ) {
			switch ( $key ) {
				case 'post_title':
				case 'post_content':
				case 'post_excerpt':
					if ( ! qtn_is_localize_value( $content ) ) {
						$old_value    = get_post_field( $key, $post_id, 'edit' );
						$strings      = qtn_value_to_localize_array( $old_value );
						$value        = qtn_set_language_value( $strings, $data[ $key ] );
						$data[ $key ] = qtn_localize_value_to_string( $value );
					}
					break;
			}
		}

		if ( empty( $data['post_name'] ) ) {
			$data['post_name'] = sanitize_title( qtn_translate_value( $data['post_title'], $qtn_config->languages[ $qtn_config->default_locale ] ) );
		}

		return $data;
	}
}
