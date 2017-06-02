<?php
/**
 * Class for capability with All in One SEO Pack
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'AIOSEOP_VERSION' ) ) {
	/**
	 * @class    WPM_AIOSP
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @since    1.2.0
	 */
	class WPM_AIOSP {

		public $meta_fields = array(
			'_aioseop_title'       => '',
			'_aioseop_description' => '',
			'_aioseop_keywords'    => ''
		);

		/**
		 * WPM_AIOSP constructor.
		 */
		public function __construct() {
			add_filter( 'wpm_option_aioseop_options_config', array( $this, 'set_posts_config' ) );
			add_filter( 'delete_post_metadata', array( $this, 'save_old_fields' ), 10, 5 );
			add_filter( 'add_post_metadata', array( $this, 'update_old_fields' ), 10, 5 );
			add_filter( 'aioseop_title', 'wpm_translate_string', 0 );
			add_action( 'wp_loaded', array( $this, 'translate_options' ) );
		}

		/**
		 * Add dynamically title setting for post types
		 *
		 * @param $config
		 *
		 * @return array
		 */
		public function set_posts_config( $config ) {

			$post_types = get_post_types();

			foreach ( $post_types as $post_type ) {
				$config["aiosp_{$post_type}_title_format"] = array();
			}

			return $config;
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

			if ( isset( $this->meta_fields[ $meta_key ] ) ) {
				global $wpdb;

				$old_value = $wpdb->get_var( $wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND post_id = %d;",
					$meta_key, $object_id ) );

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
		 * @param $unique
		 *
		 * @return bool|int
		 */
		public function update_old_fields( $check, $object_id, $meta_key, $meta_value, $unique ) {

			if ( isset( $this->meta_fields[ $meta_key ] ) && $this->meta_fields[ $meta_key ] ) {
				global $wpdb;

				$old_value  = wpm_value_to_ml_array( $this->meta_fields[ $meta_key ] );
				$meta_value = wpm_set_language_value( $old_value, $meta_value, array() );
				$meta_value = wpm_ml_value_to_string( $meta_value );


				$meta_value = maybe_serialize( $meta_value );

				$result = $wpdb->insert( $wpdb->postmeta, array(
					'post_id'    => $object_id,
					'meta_key'   => $meta_key,
					'meta_value' => $meta_value
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
		 * Translate global $aioseop_options
		 */
		public function translate_options() {
			global $aioseop_options;
			$aioseop_options = wpm_translate_value( $aioseop_options );
		}
	}

	new WPM_AIOSP();
}
