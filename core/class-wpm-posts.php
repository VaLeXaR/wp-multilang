<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Posts
 * @package  WPM\Core
 * @author   VaLeXaR
 */
class WPM_Posts extends \WPM_Object {

	/**
	 * Object name
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * Table name for meta
	 * @var string
	 */
	public $object_table = 'postmeta';


	/**
	 * WPM_Posts constructor.
	 */
	public function __construct() {
		add_filter( 'get_pages', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'posts_results', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'the_post', 'wpm_translate_object', 0 );
		add_filter( 'the_title', 'wpm_translate_string', 0 );
		add_filter( 'the_content', 'wpm_translate_string', 0 );
		add_filter( 'the_excerpt', 'wpm_translate_string', 0 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
		add_action( 'wp', array( $this, 'translate_queried_object' ), 0 );
	}


	/**
	 * Translate all posts
	 *
	 * @param $posts
	 *
	 * @return array
	 */
	public function filter_posts( $posts ) {
		return array_map( 'wpm_translate_object', $posts );
	}


	/**
	 * Translate queried object in global $wp_query
	 */
	public function translate_queried_object() {
		global $wp_query;
		if ( is_singular() ) {
			$wp_query->queried_object = wpm_translate_object( $wp_query->queried_object );
		}
	}
}
