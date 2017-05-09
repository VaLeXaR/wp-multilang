<?php
namespace QtNext\Core;

class QtN_Posts extends \QtN_Object {

	public $object_type = 'post';
	public $object_table = 'postmeta';

	public function __construct() {
		add_filter( 'the_posts', array($this, 'filter_posts'), 0, 2 );
		add_filter( 'get_pages', array($this, 'filter_posts'), 0, 2 );
		add_filter( 'the_post',   array($this, 'filter_post' ), 0, 2 );
		add_filter( 'the_title', 'qtn_localize_text', 0);
		add_filter( 'the_content', 'qtn_localize_text', 0);
		add_filter( 'get_the_excerpt', 'qtn_localize_text', 0);
		add_filter( 'single_post_title', 'qtn_localize_text', 0);
		add_filter( 'post_type_archive_title', 'qtn_localize_text', 0);
		add_filter( 'sanitize_title', 'qtn_localize_text', 0);

		add_filter( 'post_title', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'post_content', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'post_excerpt', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'get_edit_post_link', array($this, 'edit_post_link'), 0, 3);
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
	}

	public function filter_posts( $posts ) {
		$translated_posts = array();
		foreach ( $posts as $post ) {
			$translated_posts[] = qtn_translate_post( $post);
		}

		return $translated_posts;
	}

	public function filter_post( $post ) {
		return qtn_translate_post( $post );
	}

	public function translate_post_field( $value, $post_id, $context ) {

		if ( 'display' == $context ) {
			$value = qtn_localize_text( $value );
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
}
