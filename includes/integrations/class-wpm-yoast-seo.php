<?php
/**
 * Class for capability with Yoast Seo Plugin
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSEO_VERSION' ) ) {
	return;
}

/**
 * @class    WPM_Yoast_Seo
 * @package  WPM/Includes/Integrations
 * @category Integrations
 */
class WPM_Yoast_Seo {


	/**
	 * WPM_Yoast_Seo constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_wpseo_titles_config', array( $this, 'set_posts_config' ) );
		add_filter( 'wpseo_title', array( $this, 'translate_title' ) );
		remove_filter( 'update_post_metadata', array( 'WPSEO_Meta', 'remove_meta_if_default' ) );
		add_filter( 'wpseo_sitemap_url', array( $this, 'add_alternate_sitemaplinks' ), 10, 2 );
		add_filter( 'wpseo_sitemap_entry', array( $this, 'add_lang_to_url' ), 10, 3 );
		add_filter( 'wpseo_build_sitemap_post_type', array( $this, 'add_filter_for_maps' ) );
	}


	/**
	 * Add dynamically title setting for post types
	 *
	 * @param array $option_config
	 *
	 * @return array
	 */
	public function set_posts_config( $option_config ) {

		$config     = wpm_get_config();
		$post_types = $config['post_types'];

		foreach ( $post_types as $post_type => $post_config ) {
			$option_post_config = array(
				"title-{$post_type}"              => array(),
				"metadesc-{$post_type}"           => array(),
				"metakey-{$post_type}"            => array(),
				"title-ptarchive-{$post_type}"    => array(),
				"metadesc-ptarchive-{$post_type}" => array(),
			);

			$option_config = wpm_array_merge_recursive( $option_post_config, $post_config );
		}

		$taxonomies = $config['taxonomies'];

		foreach ( $taxonomies as $taxonomy => $taxonomy_config ) {
			$option_taxonomy_config = array(
				"title-tax-{$taxonomy}"    => array(),
				"metadesc-tax-{$taxonomy}" => array(),
			);

			$option_config = wpm_array_merge_recursive( $option_taxonomy_config, $option_config );
		}

		return $option_config;
	}


	/**
	 * Translate page title
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function translate_title( $title ) {
		$separator   = wpseo_replace_vars( '%%sep%%', array() );
		$separator   = ' ' . trim( $separator ) . ' ';
		$titles_part = explode( $separator, $title );
		$titles_part = wpm_translate_value( $titles_part );
		$title       = implode( $separator, $titles_part );

		return $title;
	}


	/**
	 * Add filter for each type
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function add_filter_for_maps( $type ) {
		add_filter( "wpseo_sitemap_{$type}_urlset", array( $this, 'add_namespace_to_xml' ) );
		return $type;
	}


	/**
	 * Add namespace for xmlns:xhtml
	 *
	 * @param $urlset
	 *
	 * @return mixed
	 */
	public function add_namespace_to_xml( $urlset ) {
		$urlset = str_replace( 'http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd', 'http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd', $urlset );
		$urlset = str_replace( 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml"', $urlset );

		return $urlset;
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

		foreach ( wpm_get_languages() as $lang => $language ) {

			if ( isset( $url['languages'] ) && ! in_array( $lang, $url['languages'] ) ) {
				continue;
			}

			$alternate = '';
			$new_loc   = str_replace( $url['loc'], esc_url( wpm_translate_url( $url['loc'], $lang ) ), $loc );

			foreach ( wpm_get_languages() as $lc => $lg ) {
				if ( isset( $url['languages'] ) && ! in_array( $lc, $url['languages'] ) ) {
					continue;
				}

				$alternate .= sprintf( "\t<xhtml:link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n\t", esc_attr( str_replace( '_', '-', strtolower( $lg['locale'] ) ) ), esc_url( wpm_translate_url( $url['loc'], $lc ) ) );
			}

			$alternate  = apply_filters( 'wpm_sitemap_alternate_links', $alternate, $url['loc'], $lang );
			$new_loc    = str_replace( '</url>', $alternate . '</url>', $new_loc );
			$new_output .= $new_loc;
		}

		return $new_output;
	}
}

new WPM_Yoast_Seo();
