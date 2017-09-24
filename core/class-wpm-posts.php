<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once( 'abstracts/abstract-wpm-object.php' );

/**
 * Class WPM_Posts
 * @package  WPM\Core
 * @author   VaLeXaR
 * @version  1.1.5
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
		add_filter( 'get_pages', array( $this, 'translate_posts' ), 0 );
		add_filter( 'posts_results', array( $this, 'translate_posts' ), 0 );
		add_action( 'parse_query', array( $this, 'filter_posts_by_language' ) );
		add_filter( 'the_post', 'wpm_translate_object', 0 );
		add_filter( 'the_title', 'wpm_translate_string', 0 );
		add_filter( 'the_content', 'wpm_translate_string', 0 );
		add_filter( 'the_excerpt', 'wpm_translate_string', 0 );
		add_filter( 'the_editor_content', 'wpm_translate_string', 0 );
		add_filter( 'attribute_escape', array( __CLASS__, 'escaping_text' ), 0 );
		add_filter( 'esc_textarea', array( __CLASS__, 'escaping_text' ), 0 );
		add_filter( 'esc_html', array( __CLASS__, 'escaping_text' ), 0 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
		add_action( 'wp', array( $this, 'translate_queried_object' ), 0 );
		add_filter( 'wp_insert_post_data', array( $this, 'save_post' ), 99, 2 );
		add_filter( 'wp_insert_attachment_data', array( $this, 'save_post' ), 99, 2 );
		add_filter( 'wp_get_attachment_link', array( $this, 'translate_attachment_link' ), 0 );
	}


	/**
	 * Translate all posts
	 *
	 * @param $posts
	 *
	 * @return array
	 */
	public function translate_posts( $posts ) {
		return array_map( 'wpm_translate_object', $posts );
	}

	/**
	 * Separate posts py languages
	 *
	 * @param $query object WP_Query
	 *
	 * @return object WP_Query
	 */
	public function filter_posts_by_language( $query ) {
		if ( ( ! is_admin() || wp_doing_ajax() ) && ! defined( 'DOING_CRON' ) ) {

			if ( isset( $query->query_vars['post_type'] ) && ! empty( $query->query_vars['post_type'] ) ) {

				$post_type = $query->query_vars['post_type'];

				if ( is_string( $post_type ) ) {

					$config       = wpm_get_config();
					$posts_config = $config['post_types'];

					if ( is_null( $posts_config[ $post_type ] ) ) {
						return $query;
					}
				}
			}

			$lang = get_query_var( 'lang' );

			if ( ! $lang && ! $query->is_main_query() ) {
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
							'value'   => 's:' . strlen( $lang ) . ':"' . $lang . '";',
							'compare' => 'LIKE',
						),
					),
				);

				if ( isset( $query->query_vars['meta_query'] ) ) {
					$lang_meta_query = wp_parse_args( $query->query_vars['meta_query'], $lang_meta_query );
				}

				$query->set( 'meta_query', $lang_meta_query );
			}
		} // End if().

		return $query;
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


	/**
	 * Update post with translation
	 *
	 * @param $data
	 * @param $postarr
	 *
	 * @return mixed
	 */
	public function save_post( $data, $postarr ) {

		$config                             = wpm_get_config();
		$posts_config                       = $config['post_types'];

		if ( is_null( $posts_config[ $data['post_type'] ] ) ) {
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

		$post_id = isset( $data['ID'] ) ? wpm_clean( $data['ID'] ) : ( isset( $postarr['ID'] ) ? wpm_clean( $postarr['ID'] ) : 0 );

		$post_config = $posts_config[ $data['post_type'] ];

		$default_fields = array(
			'post_title'   => array(),
			'post_excerpt' => array(),
			'post_content' => array(),
		);

		$post_config = wpm_array_merge_recursive( $default_fields, $post_config );

		foreach ( $data as $key => $content ) {
			if ( isset( $post_config[ $key ] ) ) {

				$post_field_config = apply_filters( "wpm_post_{$data['post_type']}_field_{$key}_config", $post_config[ $key ], $content );
				$post_field_config = apply_filters( "wpm_post_field_{$key}_config", $post_field_config, $content );

				if ( $post_id ) {
					$old_value = get_post_field( $key, $post_id, 'edit' );
					$old_value = wpm_value_to_ml_array( $old_value );
				} else {
					$old_value = '';
				}

				$value        = wpm_set_language_value( $old_value, $data[ $key ], $post_field_config );
				$data[ $key ] = wpm_ml_value_to_string( $value );
			}
		}

		if ( 'nav_menu_item' === $data['post_type'] ) {
			$screen = get_current_screen();

			if ( 'POST' === $_SERVER['REQUEST_METHOD'] && 'update' === $_POST['action'] && 'nav-menus' === $screen->id ) {
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

	/**
	 * Translate escaping text
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function escaping_text( $string ) {
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
			$string = wpm_translate_string( $string );
		}

		return $string;
	}
}
