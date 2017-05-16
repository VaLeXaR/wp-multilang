<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Posts extends \WPM_Object {

	public $object_type = 'post';
	public $object_table = 'postmeta';

	public function __construct() {
		add_filter( 'the_posts', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'get_pages', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'the_post', 'wpm_translate_object', 0 );
		add_filter( 'the_title', 'wpm_translate_string', 0);
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
	}


	public function filter_posts( $posts ) {
		return array_map( 'wpm_translate_object', $posts );
	}
}
