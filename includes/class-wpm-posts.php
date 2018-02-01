<?php

namespace WPM\Includes;
use WPM\Includes\Abstracts\WPM_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Posts
 * @package  WPM/Includes
 * @author   Valentyn Riaboshtan
 */
class WPM_Posts extends WPM_Object {

	/**
	 * Object name
	 *
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * Table name for meta
	 *
	 * @var string
	 */
	public $object_table = 'postmeta';


	/**
	 * WPM_Posts constructor.
	 */
	public function __construct() {
		add_filter( 'get_pages', array( $this, 'translate_posts' ), 5 );
		add_filter( 'posts_results', array( $this, 'translate_posts' ), 5 );
		add_filter( 'post_title', 'wpm_translate_string', 5 );
		add_filter( 'post_excerpt', 'wpm_translate_value', 5 );
		add_filter( 'post_content', 'wpm_translate_value', 5 );
		add_filter( 'the_post', 'wpm_translate_post', 5 );
		add_filter( 'the_title', 'wpm_translate_string', 5 );
		add_filter( 'the_content', 'wpm_translate_string', 5 );
		add_filter( 'the_excerpt', 'wpm_translate_string', 5 );
		add_filter( 'the_editor_content', 'wpm_translate_string', 5 );
		add_action( 'parse_query', array( $this, 'filter_posts_by_language' ) );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 5, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
		add_action( "delete_{$this->object_type}_metadata", array( $this, 'delete_meta_field' ), 99, 3 );
		add_action( 'wp', array( $this, 'translate_queried_object' ), 5 );
		add_filter( 'wp_insert_post_data', array( $this, 'save_post' ), 99, 2 );
		add_filter( 'wp_insert_attachment_data', array( $this, 'save_post' ), 99, 2 );
		add_filter( 'wp_get_attachment_link', array( $this, 'translate_attachment_link' ), 5 );
	}


	/**
	 * Translate all posts
	 *
	 * @param $posts
	 *
	 * @return array
	 */
	public function translate_posts( $posts ) {
		foreach ( $posts as &$post ) {
			$post = wpm_translate_post( $post );
		}

		return $posts;
	}

	/**
	 * Separate posts py languages
	 *
	 * @param $query object WP_Query
	 *
	 * @return object WP_Query
	 */
	public function filter_posts_by_language( $query ) {

		if ( defined( 'DOING_CRON' ) || ( is_admin() && ! is_front_ajax() ) ) {
			return $query;
		}

		if ( isset( $query->query_vars['post_type'] ) && ! empty( $query->query_vars['post_type'] ) ) {
			$post_type = $query->query_vars['post_type'];
			if ( is_string( $post_type ) ) {
				if ( null === wpm_get_post_config( $post_type ) ) {
					return $query;
				}
			}
		}

		$lang = get_query_var( 'lang' );

		if ( ! $lang ) {
			$lang = wpm_get_user_language();
		}

		if ( 'all' !== $lang ) {
			$lang_meta_query = array(
				array(
					'relation' => 'OR',
					array(
						'key'     => '_languages',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_languages',
						'value'   => serialize( $lang ),
						'compare' => 'LIKE',
					),
				),
			);

			if ( isset( $query->query_vars['meta_query'] ) ) {
				$lang_meta_query = wp_parse_args( $query->query_vars['meta_query'], $lang_meta_query );
			}

			$query->set( 'meta_query', $lang_meta_query );
		}

		return $query;
	}


	/**
	 * Translate queried object in global $wp_query
	 */
	public function translate_queried_object() {
		global $wp_query;

		if ( ( $post = $wp_query->queried_object ) && ( is_singular() || is_home() ) ) {
			if (  null !== wpm_get_post_config( $post->post_type ) ) {
				$wp_query->queried_object = wpm_translate_post( $post );
			}
		}
	}


	/**
	 * Update post with translation
	 *
	 * @param $data
	 * @param $postarr
	 *
	 * @return mixed
	 */
	public function save_post( $data, $postarr ) {

		if ( 'auto-draft' === $data['post_status'] ) {
			return $data;
		}

		$post_config = wpm_get_post_config( $data['post_type'] );

		if ( null === $post_config ) {
			return $data;
		}

		if ( 'attachment' !== $data['post_type'] ) {

			if ( 'trash' === $postarr['post_status'] ) {
				return $data;
			}

			if ( isset( $_GET['action'] ) && 'untrash' === $_GET['action'] ) {
				return $data;
			}
		}

		$post_id = isset( $data['ID'] ) ? wpm_clean( $data['ID'] ) : ( isset( $postarr['ID'] ) ? wpm_clean( $postarr['ID'] ) : 0 );

		foreach ( $data as $key => $content ) {
			if ( isset( $post_config[ $key ] ) ) {

				$post_field_config = apply_filters( "wpm_post_{$data['post_type']}_field_{$key}_config", $post_config[ $key ], $content );
				$post_field_config = apply_filters( "wpm_post_field_{$key}_config", $post_field_config, $content );

				if ( $post_id ) {
					$old_value = get_post_field( $key, $post_id, 'edit' );
				} else {
					$old_value = '';
				}

				if ( ! wpm_is_ml_value( $data[ $key ] ) ) {
					$data[ $key ] = wpm_set_new_value( $old_value, $data[ $key ], $post_field_config );
				}
			}
		}

		if ( 'nav_menu_item' === $data['post_type'] ) {
			$screen = get_current_screen();

			if ( 'POST' === $_SERVER['REQUEST_METHOD'] && 'update' === $_POST['action'] && ( $screen && 'nav-menus' === $screen->id ) ) {
				// hack to get wp to create a post object when too many properties are empty
				if ( '' === $data['post_title'] && '' === $data['post_content'] ) {
					$data['post_content'] = ' ';
				}
			}
		}

		if ( empty( $data['post_name'] ) ) {
			$data['post_name'] = sanitize_title( wpm_translate_value( $data['post_title'] ) );
		}

		return $data;
	}


	/**
	 * Translate attachment link
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function translate_attachment_link( $link ) {
		$text            = strip_tags( $link );
		$translated_text = wpm_translate_string( $text );

		return str_replace( $text, $translated_text, $link );
	}
}
