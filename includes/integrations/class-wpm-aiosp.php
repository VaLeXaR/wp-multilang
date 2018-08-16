<?php
/**
 * Class for capability with All in One SEO Pack
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class    WPM_AIOSP
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_AIOSP {

	public $meta_fields = array(
		'_aioseop_title'       => '',
		'_aioseop_description' => '',
		'_aioseop_keywords'    => '',
	);

	/**
	 * WPM_AIOSP constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_aioseop_options_config', array( $this, 'set_posts_config' ) );
		add_filter( 'delete_post_metadata', array( $this, 'save_old_fields' ), 10, 5 );
		add_filter( 'add_post_metadata', array( $this, 'update_old_fields' ), 10, 4 );
		add_filter( 'init', array( $this, 'translate_options' ) );

		// AIOSP Sitemap do not support simple tag in sitemap like "xhtml:link" what needed in multilingual sitemap
		//add_filter( 'aiosp_sitemap_xml_namespace', array( $this, 'add_namespace' ) );
	}

	/**
	 * Translate options array on init.
	 */
	public function translate_options() {
		global $aioseop_options;
		$aioseop_options = wpm_translate_value( $aioseop_options );
	}

	/**
	 * Add dynamically title setting for post types
	 *
	 * @param array $option_config
	 *
	 * @return array
	 */
	public function set_posts_config( $option_config ) {

		$post_types = get_post_types( array(), 'names' );

		foreach ( $post_types as $post_type ) {

			if ( null === wpm_get_post_config( $post_type ) ) {
				continue;
			}

			$option_config[ "aiosp_{$post_type}_title_format" ] = array();
		}

		return $option_config;
	}

	/**
	 * Save old translation before deleting
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param $delete_all
	 *
	 * @return mixed
	 */
	public function save_old_fields( $check, $object_id, $meta_key, $meta_value, $delete_all ) {

		if ( $delete_all ) {
			return $check;
		}

		if ( null === wpm_get_post_config( get_post_type( $object_id ) ) ) {
			return $check;
		}

		if ( isset( $this->meta_fields[ $meta_key ] ) ) {
			global $wpdb;

			$old_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id = %d;", $meta_key, $object_id ) );

			if ( $old_value ) {
				$this->meta_fields[ $meta_key ] = $old_value;
			}
		}

		return $check;
	}

	/**
	 * Add field with new and old translations
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $meta_value
	 *
	 * @return bool|int
	 */
	public function update_old_fields( $check, $object_id, $meta_key, $meta_value ) {
		global $wpdb;

		if ( null === wpm_get_post_config( get_post_type( $object_id ) ) ) {
			return $check;
		}

		if ( ! empty( $this->meta_fields[ $meta_key ] ) ) {

			$meta_value = wpm_set_new_value( $this->meta_fields[ $meta_key ], $meta_value );
			$meta_value = maybe_serialize( $meta_value );

			$result = $wpdb->insert( $wpdb->postmeta, array(
				'post_id'    => $object_id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			) );

			if ( ! $result ) {
				return false;
			}

			$mid = (int) $wpdb->insert_id;

			wp_cache_delete( $object_id, 'post_meta' );

			return $mid;
		}

		return $check;
	}

	/**
	 * Add namespace to sitemap
	 *
	 * @since 2.0.0
	 *
	 * @param $namespace
	 *
	 * @return mixed
	 */
	public function add_namespace( $namespace ) {
		$namespace['xmlns:xhtml'] = 'http://www.w3.org/1999/xhtml';

		return $namespace;
	}
}
