<?php
namespace QtNext\Core;

class QtN_Posts {

	private $post_types = array('post', 'page');

	public function __construct() {
		add_filter( 'the_posts', array($this, 'filter_posts'), 0, 2 );
		add_filter( 'get_pages', array($this, 'filter_posts'), 0, 2 );
		add_filter( 'the_title', array($this, 'translate_field'), 0);
		add_filter( 'the_content', array($this, 'translate_field'), 0);
		add_filter( 'get_the_excerpt', array($this, 'translate_field'), 0);
		add_filter( 'single_post_title', array($this, 'translate_field'), 0);
		add_filter( 'post_type_archive_title', array($this, 'translate_field'), 0);

		add_filter( 'post_title', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'post_content', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'post_excerpt', array($this, 'translate_post_field'), 0, 3);
		add_filter( 'get_edit_post_link', array($this, 'edit_post_link'), 0, 3);
		//TODO filter for post types
	}

	public function filter_posts( $posts ) {
		$translated_posts = array();
		foreach ( $posts as $post ) {
			$translated_posts[] = qtn_translate_post( $post);
		}

		return $translated_posts;
	}

	public function translate_field($value) {
		return qtn_localize_text($value);
	}

	public function translate_post_field( $value, $post_id, $context ) {

		if ( 'display' == $context ) {
			$value = qtn_localize_text( $value );
		}

		return $value;
	}

	public function edit_post_link( $link, $post_id, $context ) {
		global $locale, $qtn_config;
		$post_type = get_post_type($post_id);
		if ( 'display' == $context && in_array( $post_type, $this->post_types) ) {
			$link = add_query_arg( 'edit_lang', $qtn_config->languages[ $locale ], $link );
		}

		return $link;
	}

}
