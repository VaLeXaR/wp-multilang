<?php

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WPSEO_VERSION' ) ) {

	class WPM_Yoast_Seo {

		public function __construct() {
			add_filter( 'wpm_option_wpseo_titles_config', array( $this, 'set_posts_config' ) );
			add_filter( 'wpseo_title', 'wpm_translate_string', 0 );
			add_filter( 'wpseo_metadesc', 'wpm_translate_string', 0 );
			add_filter( 'wpseo_metakey', 'wpm_translate_string', 0 );
			add_filter( 'wpseo_metakeywords', 'wpm_translate_string', 0 );
		}


		public function set_posts_config( $config ) {

			$post_types = get_post_types();

			foreach ( $post_types as $post_type ) {
				$post_config = array(
					"title-{$post_type}"              => array(),
					"metadesc-{$post_type}"           => array(),
					"title-ptarchive-{$post_type}"    => array(),
					"metadesc-ptarchive-{$post_type}" => array(),
				);

				$config = wpm_array_merge_recursive( $config, $post_config );
			}

			$taxonomies = get_taxonomies();

			foreach ( $taxonomies as $taxonomy ) {
				$tax_config = array(
					"title-tax-{$taxonomy}"    => array(),
					"metadesc-tax-{$taxonomy}" => array()
				);

				$config = wpm_array_merge_recursive( $config, $tax_config );
			}

			return $config;
		}
	}

	new WPM_Yoast_Seo();
}
