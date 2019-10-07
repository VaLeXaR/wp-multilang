<?php
/**
 * Class for capability with Rank Math Seo Plugin
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class    WPM_Rank_Math
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Rank_Math {

	/**
	 * WPM_Rank_Math constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_rank-math-options-titles_config', array( $this, 'set_posts_config' ) );
		add_filter( 'rank_math/sitemap/url', array( $this, 'add_alternate_sitemaplinks' ), 10, 2 );
		add_filter( 'rank_math/sitemap/entry', array( $this, 'add_lang_to_url' ), 10, 3 );
		add_filter( 'rank_math/sitemap/build_type', array( $this, 'add_filter_for_maps' ) );
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

			$option_post_config = array(
				"pt_{$post_type}_title"                => array(),
				"pt_{$post_type}_description"          => array(),
				"pt_{$post_type}_default_rich_snippet" => array(),
				"pt_{$post_type}_default_snippet_name" => array(),
				"pt_{$post_type}_default_snippet_desc" => array(),
				"pt_{$post_type}_default_article_type" => array(),
				"pt_{$post_type}_custom_robots"        => array(),
				"pt_{$post_type}_link_suggestions"     => array(),
				"pt_{$post_type}_ls_use_fk"            => array(),
				"pt_{$post_type}_primary_taxonomy"     => array(),
				"pt_{$post_type}_add_meta_box"         => array(),
				"pt_{$post_type}_bulk_editing"         => array(),
			);

			$option_config = wpm_array_merge_recursive( $option_post_config, $option_config );
		}

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( null === wpm_get_taxonomy_config( $taxonomy ) ) {
				continue;
			}

			$option_taxonomy_config = array(
				"tax_{$taxonomy}_title"        => array(),
				"tax_{$taxonomy}_description"     => array(),
				"tax_{$taxonomy}_custom_robots"   => array(),
				"tax_{$taxonomy}_robots"          => array(),
				"tax_{$taxonomy}_add_meta_box"    => array(),
				"remove_{$taxonomy}_snippet_data" => array(),
			);

			$option_config = wpm_array_merge_recursive( $option_taxonomy_config, $option_config );
		}

		return $option_config;
	}

	/**
	 * Add filter for each type
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function add_filter_for_maps( $type ) {
		add_filter( "rank_math/sitemap/{$type}_urlset", array( $this, 'add_namespace_to_xml' ) );
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
		$urlset = str_replace(
			array(
				'http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd',
				'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"'
			),
			array(
				'http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd',
				'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xhtml="http://www.w3.org/1999/xhtml"'
			),
			$urlset );

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

		foreach ( wpm_get_languages() as $code => $language ) {

			if ( isset( $url['languages'] ) && ! in_array( $code, $url['languages'] ) ) {
				continue;
			}

			$alternate = array();
			$new_loc   = str_replace( $url['loc'], esc_url( wpm_translate_url( $url['loc'], $code ) ), $loc );

			foreach ( wpm_get_languages() as $key => $lg ) {
				if ( isset( $url['languages'] ) && ! in_array( $key, $url['languages'] ) ) {
					continue;
				}

				$alternate[ $key ] = sprintf( "\t<xhtml:link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n\t", esc_attr( wpm_sanitize_lang_slug( $lg['locale'] ) ), esc_url( wpm_translate_url( $url['loc'], $key ) ) );
			}

			$alternate  = apply_filters( 'wpm_sitemap_alternate_links', $alternate, $url['loc'], $code );
			$new_loc    = str_replace( '</url>', implode( '', $alternate ) . '</url>', $new_loc );
			$new_output .= $new_loc;
		}

		return $new_output;
	}
}
