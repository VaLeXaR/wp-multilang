<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once( 'abstracts/abstract-wpm-object.php' );

/**
 * Class WPM_Taxonomies
 * @package  WPM\Core
 * @author   VaLeXaR
 */
class WPM_Taxonomies extends \WPM_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';
	private $description = array();


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
		add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
		add_filter( 'wp_insert_term_data', array( $this, 'insert_term' ), 99, 3 );
		add_action( 'created_term', array( $this, 'insert_description' ), 99, 3 );
		add_filter( 'wp_update_term_data', array( $this, 'update_term' ), 99, 4 );
		add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );
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

					$config            = wpm_get_config();
					$taxonomies_config = $config['taxonomies'];

					if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
						return $args;
					}
				}
			}

			if ( isset( $args['lang'] ) && ! empty( $args['lang'] ) ) {
				$lang = $args['lang'];
			} else {
				$lang = wpm_get_language();
			}

			if ( 'all' != $lang ) {
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


	/**
	 * Check if isset taxonomies in current language
	 *
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return string
	 */
	public function pre_insert_term( $term, $taxonomy ) {
		global $wpdb;

		$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

		foreach ( $results as $result ) {
			$ml_term = wpm_translate_string( $result->name );
			if ( $ml_term === $term ) {
				return '';
			}
		}

		return $term;
	}


	/**
	 * Translate inserted term
	 *
	 * @param $data
	 * @param $taxonomy
	 * @param $args
	 *
	 * @return mixed
	 */
	public function insert_term( $data, $taxonomy, $args ) {

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return $data;
		}

		if ( ! wpm_is_ml_value( $data['name'] ) ) {
			$data['name'] = wpm_set_language_value( array(), $data['name'], $taxonomies_config[ $taxonomy ] );
			$data['name'] = wpm_ml_value_to_string( $data['name'] );
		}

		$this->description = array(
			'new' => $args['description'],
		);

		return $data;
	}


	/**
	 * Update inserted description for term
	 *
	 * @param $term_id
	 * @param $tt_id
	 * @param $taxonomy
	 */
	public function insert_description( $term_id, $tt_id, $taxonomy ) {
		global $wpdb;

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return;
		}

		if ( ! $this->description ) {
			return;
		}

		$value = $this->description['new'];

		if ( wpm_is_ml_value( $value ) ) {
			return;
		}

		$value       = wpm_set_language_value( array(), $value, $taxonomies_config[ $taxonomy ] );
		$description = wpm_ml_value_to_string( $value );

		$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
	}


	/**
	 * Save taxonomies with translation
	 *
	 * @param $data
	 * @param $term_id
	 * @param $taxonomy
	 * @param $args
	 *
	 * @return mixed
	 */
	public function update_term( $data, $term_id, $taxonomy, $args ) {

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return $data;
		}

		remove_filter( 'get_term', 'wpm_translate_object', 0 );
		$old_name        = get_term_field( 'name', $term_id, $taxonomy, 'edit' );
		$old_description = get_term_field( 'description', $term_id, $taxonomy, 'edit' );
		add_filter( 'get_term', 'wpm_translate_object', 0 );

		if ( ! wpm_is_ml_value( $data['name'] ) ) {
			$strings      = wpm_value_to_ml_array( $old_name );
			$value        = wpm_set_language_value( $strings, $data['name'], $taxonomies_config[ $taxonomy ] );
			$data['name'] = wpm_ml_value_to_string( $value );
		}

		$this->description = array(
			'old' => $old_description,
			'new' => $args['description'],
		);

		return $data;
	}


	/**
	 * Fix for saving taxonomy description
	 *
	 * @param $tt_id
	 * @param $taxonomy
	 */
	public function update_description( $tt_id, $taxonomy ) {
		global $wpdb;

		$config            = wpm_get_config();
		$taxonomies_config = $config['taxonomies'];

		if ( is_null( $taxonomies_config[ $taxonomy ] ) ) {
			return;
		}

		if ( ! $this->description ) {
			return;
		}

		$value = $this->description['new'];

		if ( wpm_is_ml_value( $value ) ) {
			return;
		}

		$old_value   = $this->description['old'];
		$strings     = wpm_value_to_ml_array( $old_value );
		$value       = wpm_set_language_value( $strings, $value, $taxonomies_config[ $taxonomy ] );
		$description = wpm_ml_value_to_string( $value );

		$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
	}
}
