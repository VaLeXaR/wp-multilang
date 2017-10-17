<?php
/**
 * Class for capability with Yoast Seo Plugin
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPSEO_VERSION' ) ) {
	return;
}

/**
 * @class    WPM_Yoast_Seo
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @version  1.0.1
 */
class WPM_Yoast_Seo {


	/**
	 * WPM_Yoast_Seo constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_wpseo_titles_config', array( $this, 'set_posts_config' ) );
		add_filter( 'wpseo_title', 'wpm_translate_string', 0 );
		remove_filter( 'update_post_metadata', array( 'WPSEO_Meta', 'remove_meta_if_default' ), 10 );
		add_filter( 'wpseo_enable_xml_sitemap_transient_caching', '__return_false' );
		add_filter( 'wpseo_sitemap_url', array( $this, 'add_alternate_sitemaplinks' ), 10, 2 );
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
	 * Add alternate links to sitemap
	 *
	 * @param string $output
	 * @param array $url
	 *
	 * @return string
	 */
	public function add_alternate_sitemaplinks( $output, $url ) {
		$alternate = '';
		foreach ( wpm_get_languages() as $locale => $language ) {
			$alternate .= sprintf( "\t<xhtml:link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n\t",
				esc_attr( str_replace( '_', '-', strtolower( $locale ) ) ),
				esc_url( wpm_translate_url( $url['loc'], $language ) ) );
		}
		$alternate .= '</url>';
		$output    = str_replace( '</url>', $alternate, $output );

		return $output;
	}
}

new WPM_Yoast_Seo();
