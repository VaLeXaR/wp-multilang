<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Taxonomies
 * @package  WPM\Core
 * @author   VaLeXaR
 */
class WPM_Taxonomies extends \WPM_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';


	/**
	 * WPM_Taxonomies constructor.
	 */
	public function __construct() {
		add_filter( 'get_term', 'wpm_translate_object', 0 );
		add_filter( 'get_terms', array( $this, 'translate_terms' ), 0 );
		add_filter( 'get_terms_args', array( $this, 'filter_terms_by_language' ), 10, 2 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
		add_filter( "add_{$this->object_type}_metadata", array( $this, 'add_meta_field' ), 99, 5 );
	}


	/**
	 * Translate all terms
	 *
	 * @param $terms
	 *
	 * @return array
	 */
	public function translate_terms( $terms ) {

		if ( is_array( $terms ) ) {
			$_terms = array();
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {
					$_terms[] = $term;
				} else {
					$_terms[] = wpm_translate_value( $term );
				}
			}
			$terms = $_terms;
		}

		return $terms;
	}


	/**
	 * Separate taxonomies by language
	 *
	 * @param $args
	 * @param $taxonomies
	 *
	 * @return mixed
	 */
	public function filter_terms_by_language( $args, $taxonomies ) {

		if ( ( ! is_admin() || wp_doing_ajax() ) && ! defined( 'DOING_CRON' ) ) {

			if ( ! empty( $taxonomies ) ) {

				if ( count( $taxonomies ) === 1 ) {
					$taxonomy = current( $taxonomies );

					$config                         = wpm_get_config();
					$taxonomies_config              = $config['taxonomies'];
					$taxonomies_config              = apply_filters( 'wpm_taxonomies_config', $taxonomies_config );
					$taxonomies_config[ $taxonomy ] = apply_filters( "wpm_taxonomy_{$taxonomy}_config", isset( $taxonomies_config[ $taxonomy ] ) ? $taxonomies_config[ $taxonomy ] : null );

					if ( ! isset( $config['taxonomies'][ $taxonomy ] ) || is_null( $config['taxonomies'][ $taxonomy ] ) ) {
						return $args;
					}
				}
			}

			$lang = get_query_var( 'lang' );

			if ( ! $lang ) {
				$lang = wpm_get_user_language();
			}

			if ( $lang ) {
				$lang_meta_query = array(
					array(
						'relation' => 'OR',
						array(
							'key'     => '_languages',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => '_languages',
							'value'   => 's:' . strlen( $lang ) . ':"' . $lang . '";',
							'compare' => 'LIKE',
						),
					),
				);

				if ( isset( $args['meta_query'] ) ) {
					$args['meta_query'] = wp_parse_args( $args['meta_query'], $lang_meta_query );
				} else {
					$args['meta_query'] = $lang_meta_query;
				}
			}
		} // End if().

		return $args;
	}
}
