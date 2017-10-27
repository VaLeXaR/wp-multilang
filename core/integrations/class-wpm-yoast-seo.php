<?php
/**
 * Class for capability with Yoast Seo Plugin
 */

namespace WPM\Core\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSEO_VERSION' ) ) {
	return;
}

/**
 * @class    WPM_Yoast_Seo
 * @package  WPM\Core\Integrations
 * @category Integrations
 * @author   VaLeXaR
 */
class WPM_Yoast_Seo {


	/**
	 * WPM_Yoast_Seo constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_wpseo_titles_config', array( $this, 'set_posts_config' ) );
		add_filter( 'wpseo_title', 'wpm_translate_string', 0 );
		remove_filter( 'update_post_metadata', array( 'WPSEO_Meta', 'remove_meta_if_default' ), 10 );
		add_filter( 'wpseo_sitemap_url', array( $this, 'add_alternate_sitemaplinks' ), 10, 2 );
		add_filter( 'wpseo_sitemap_entry', array( $this, 'add_lang_to_url' ), 10, 3 );
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
			$post_config = array(
				"title-{$post_type}"              => array(),
				"metadesc-{$post_type}"           => array(),
				"metakey-{$post_type}"           => array(),
				"title-ptarchive-{$post_type}"    => array(),
				"metadesc-ptarchive-{$post_type}" => array(),
			);

			$config = wpm_array_merge_recursive( $config, $post_config );
		}

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			$tax_config = array(
				"title-tax-{$taxonomy}"    => array(),
				"metadesc-tax-{$taxonomy}" => array(),
			);

			$config = wpm_array_merge_recursive( $config, $tax_config );
		}

		return $config;
	}


	/**
	 * Add separating by language to url
	 *
	 * @param array $url
	 * @param string $type
	 * @param object $object
	 *
	 * @return array
	 */
	public function add_lang_to_url( $url, $type, $object ) {

		$languages = array();

		switch ( $type ) {
			case 'post':
				$languages = get_post_meta( $object->ID, '_languages', true );
				break;
			case 'term':
				$languages = get_term_meta( $object->term_id, '_languages', true );
				break;
		}

		if ( $languages ) {
			$url['languages'] = $languages;
		}

		return $url;
	}


	/**
	 * Add alternate links to sitemap
	 *
	 * @param string $output
	 * @param array $url
	 *
	 * @return string
	 */
	public function add_alternate_sitemaplinks( $output, $url ) {
		$loc        = $output;
		$new_output = '';

		foreach ( wpm_get_languages() as $locale => $language ) {

			if ( isset( $url['languages'] ) && ! in_array( $language, $url['languages'] ) ) {
				continue;
			}

			$alternate = '';
			$new_loc   = str_replace( $url['loc'], esc_url( wpm_translate_url( $url['loc'], $language ) ), $loc );

			foreach ( wpm_get_languages() as $lc => $lg ) {
				if ( isset( $url['languages'] ) && ! in_array( $lg, $url['languages'] ) ) {
					continue;
				}

				$alternate .= sprintf( "\t<xhtml:link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n\t", esc_attr( str_replace( '_', '-', strtolower( $lc ) ) ), esc_url( wpm_translate_url( $url['loc'], $lg ) ) );
			}

			$new_loc    = str_replace( '</url>', $alternate . '</url>', $new_loc );
			$new_output .= $new_loc;
		}

		return $new_output;
	}
}

new WPM_Yoast_Seo();
