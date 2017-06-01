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
	 * @category Class
	 * @author   VaLeXaR
	 */
	class WPM_AIOSP {

		public $meta_fields = array(
			'_aioseop_title',
			'_aioseop_description',
			'_aioseop_keywords'
		);

		/**
		 * WPM_AIOSP constructor.
		 */
		public function __construct() {
			add_filter( 'wpm_option_aioseop_options_config', array( $this, 'set_posts_config' ) );
			add_filter( 'delete_post_metadata', array( $this, 'do_not_delete_old_fields' ), 10, 5 );
//			add_filter( 'wpseo_title', 'wpm_translate_string', 0 );
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


		public function do_not_delete_old_fields( $check, $object_id, $meta_key, $meta_value, $delete_all ) {

			if ( $delete_all ) {
				return $check;
			}

			if ( in_array( $meta_key, $this->meta_fields ) ) {
				return true;
			}

			return $check;
		}
	}

	new WPM_AIOSP();
}
