<?php

namespace WPM\Includes;
use WPM\Includes\Abstracts\WPM_Object;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Comments
 *
 * @package  WPM/Classes
 * @author   Valentyn Riaboshtan
 */
class WPM_Comments extends WPM_Object {

	/**
	 * Object name
	 *
	 * @var string
	 */
	public $object_type = 'comment';

	/**
	 *Table name for meta
	 *
	 * @var string
	 */
	public $object_table = 'commentmeta';

	/**
	 * WPM_Comments constructor.
	 */
	public function __construct() {
		add_action( 'parse_comment_query', array( $this, 'filter_comments_by_language' ) );
		add_filter( 'get_comments_number', array( $this, 'fix_comment_count' ), 10, 2 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 5, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
		add_action( "delete_{$this->object_type}_metadata", array( $this, 'delete_meta_field' ), 99, 3 );
	}

	/**
	 * Separate comments py languages
	 *
	 * @param $query object WP_Comment_Query
	 *
	 * @return object WP_Comment_Query
	 */
	public function filter_comments_by_language( $query ) {

		if ( defined( 'DOING_CRON' ) || ( is_admin() && ! is_front_ajax() ) ) {
			return $query;
		}

		$lang = get_query_var( 'lang' );

		if ( ! $lang ) {
			$lang = wpm_get_user_language();
		}

		if ( 'all' !== $lang ) {
			$meta_query = array(
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
				$meta_query = wp_parse_args( $query->query_vars['meta_query'], $meta_query );
			}

			$query->query_vars['meta_query'] = $meta_query;
		}

		return $query;
	}

	public function fix_comment_count( $count, $post_id ) {

		if ( defined( 'DOING_CRON' ) || ( is_admin() && ! is_front_ajax() ) ) {
			return $count;
		}

		$lang = get_query_var( 'lang' );

		if ( ! $lang ) {
			$lang = wpm_get_user_language();
		}

		$count_array = wp_cache_get( $post_id, 'wpm_comment_count' );

		if ( isset( $count_array[ $lang ] ) ) {
			return $count_array[ $lang ];
		} else {
			$count_array = array();
		}

		global $wpdb;

		$meta_query = array(
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

		$meta_sql = get_meta_sql( $meta_query, 'comment', $wpdb->comments, 'comment_ID' );

		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->comments} {$meta_sql['join']} WHERE comment_post_ID = %d AND comment_approved = '1' {$meta_sql['where']};", $post_id ) );

		$count_array[ $lang ] = $count;
		wp_cache_add( $post_id, $count_array, 'wpm_comment_count' );

		return $count;
	}
}
