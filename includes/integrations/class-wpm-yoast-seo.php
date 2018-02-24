<?php
/**
 * Class for capability with Yoast Seo Plugin
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class    WPM_Yoast_Seo
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
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


		$options = \WPSEO_Options::get_option( 'wpseo_social' );

		if ( true === $options['opengraph'] ) {
			add_action( 'wpm_language_settings', array( $this, 'set_opengraph_locale' ), 10, 2 );
			add_filter( 'wpm_rest_schema_languages', array( $this, 'add_schema_to_rest' ) );
			add_filter( 'wpm_save_languages', array( $this, 'save_languages' ), 10, 2 );
			add_filter( 'wpseo_locale', array( $this, 'add_opengraph_locale' ) );
			add_action( 'wpseo_opengraph', array( $this, 'add_alternate_opengraph_locale' ), 40 );
		}
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
				"title-{$post_type}"              => array(),
				"metadesc-{$post_type}"           => array(),
				"metakey-{$post_type}"            => array(),
				"title-ptarchive-{$post_type}"    => array(),
				"metadesc-ptarchive-{$post_type}" => array(),
			);

			$option_config = wpm_array_merge_recursive( $option_post_config, $option_config );
		}

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {

			if ( null === wpm_get_taxonomy_config( $taxonomy ) ) {
				continue;
			}

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

	/**
	 * Set locale for opengraph
	 *
	 * @since 2.0.3
	 *
	 * @param $count
	 * @param $lang
	 */
	public function set_opengraph_locale( $lang, $count ) {
		$options = get_option( 'wpm_languages', array() );
		$value   = '';

		if ( isset( $options[ $lang ]['wpseo_og_locale'] ) ) {
			$value = $options[ $lang ]['wpseo_og_locale'];
		}
		?>
		<tr>
			<td class="row-title"><?php esc_attr_e( 'Yoast SEO Opengraph Locale', 'wp-multilang' ); ?></td>
			<td>
				<input type="text" name="wpm_languages[<?php echo esc_attr( $count ); ?>][wpseo_og_locale]" value="<?php esc_attr_e( $value ); ?>" title="<?php esc_attr_e( 'Yoast SEO Opengraph Locale', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Opengraph Locale', 'wp-multilang' ); ?>">
				<p><?php esc_html_e( 'Locale must be with country domain. Like en_US', 'wp-multilang' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Add param to rest schema
	 *
	 * @since 2.0.3
	 *
	 * @param $schema
	 *
	 * @return mixed
	 */
	public function add_schema_to_rest( $schema ) {
		$schema['wpseo_og_locale'] = array( 'type' => 'string' );

		return $schema;
	}

	/**
	 * Save languages
	 *
	 * @since 2.0.3
	 *
	 * @param $languages
	 * @param $request
	 *
	 * @return mixed
	 */
	public function save_languages( $languages, $request ) {
		foreach ( $request as $value ) {
			if ( isset( $languages[ $value['code'] ], $value['wpseo_og_locale'] ) ) {
				$languages[ $value['code'] ]['wpseo_og_locale'] = $value['wpseo_og_locale'];
			}
		}

		return $languages;
	}

	/**
	 * Set locale for opengraph
	 *
	 * @since 2.0.0
	 *
	 * @param $locale
	 *
	 * @return string
	 */
	public function add_opengraph_locale( $locale ) {
		$languages     = wpm_get_languages();
		$user_language = wpm_get_language();

		if ( ! empty( $languages[ $user_language ]['wpseo_og_locale'] ) ) {
			$locale = $languages[ $user_language ]['wpseo_og_locale'];
		}

		return $locale;
	}

	/**
	 * Set alternate locale for opengraph
	 *
	 * @since 2.2.0
	 */
	public function add_alternate_opengraph_locale() {
		global $wpseo_og;

		$languages = array();

		if ( is_singular() ) {
			$languages = get_post_meta( get_the_ID(), '_languages', true );
		} elseif ( is_category() || is_tax() || is_tag() ) {
			$languages = get_term_meta( get_queried_object_id(), '_languages', true );
		}

		foreach ( wpm_get_languages() as $code => $language ) {

			if ( ( $languages && ! isset( $languages[ $code ] ) ) || $code === wpm_get_language() ) {
				continue;
			}

			if ( ! empty( $language['wpseo_og_locale'] ) ) {
				$wpseo_og->og_tag( 'og:locale:alternate', $language['wpseo_og_locale'] );
			}
		}
	}
}
