<?php

namespace QtNext\Core;

class QtN_Taxonomies extends \QtN_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';

	private $description = array();

	public function __construct() {

		add_filter( 'get_term', 'qtn_translate_object', 0 );
		add_filter( 'get_terms', array($this, 'filter_terms'), 0 );
		add_filter( 'pre_insert_term', array( $this, 'pre_insert_term' ), 0, 2 );
		add_filter( 'wp_update_term_data', array( $this, 'save_term' ), 0, 4 );
		add_action( 'edited_term_taxonomy', array( $this, 'update_description' ), 0, 2 );

//		add_filter( 'single_term_title', 'qtn_translate_string', 0);
//		add_filter( 'get_the_archive_title', 'qtn_translate_string', 0);
//		add_filter( 'get_the_archive_description', 'qtn_translate_string', 0);

		add_filter( 'get_terms', array($this, 'filter_terms'), 0 );

		add_filter( 'get_edit_term_link', array( $this, 'edit_term_link' ), 0, 3 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );
	}

	public function filter_terms( $terms ) {

		if ( is_array( $terms ) ) {
			$_terms = array();
			foreach ( $terms as $term ) {
				if ( is_object( $term ) ) {
					$_terms[] = $term;
				} else {
					$_terms[] = qtn_translate_value( $term );
				}
			}
			$terms = $_terms;
		}

		return $terms;
	}

	public function edit_term_link( $location, $term_id, $taxonomy ) {
		global $qtn_config;
		if ( in_array( $taxonomy, $qtn_config->settings['taxonomies'] ) ) {
			$location = add_query_arg( 'edit_lang', $qtn_config->languages[ get_locale() ], $location );
		}

		return $location;
	}

	public function pre_insert_term( $term, $taxonomy ) {
		global $wpdb, $qtn_config;

		$to_locale = '';
		$languages = array_flip( $qtn_config->languages );
		if ( isset( $_POST['lang'] ) && isset( $languages[ qtn_clean( $_POST['lang'] ) ] ) ) {
			$to_locale = $languages[ qtn_clean( $_POST['lang'] ) ];
		}

		$like    = '%' . $wpdb->esc_like( esc_sql( $term ) ) . '%';
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT t.name AS `name` FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '%s' AND `name` LIKE '%s'", $taxonomy, $like ) );

		foreach ( $results as $result ) {
			$ml_term = qtn_translate_string( $result->name, $to_locale );
			if ( $ml_term == $term ) {
				return '';
			}
		}

		return $term;
	}

	public function save_term( $data, $term_id, $taxonomy, $args ) {
		global $wpdb, $qtn_config;

		if ( ! in_array( $taxonomy, $qtn_config->settings['taxonomies'] ) ) {
			return $data;
		}

		if ( qtn_is_ml_value( $data['name'] ) ) {
			return $data;
		}

		$old_value = $wpdb->get_row( $wpdb->prepare( "SELECT t.name AS `name`, tt.description FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE t.term_id = %d;", $term_id) );

		$old_name     = $old_value->name;
		$strings      = qtn_value_to_ml_array( $old_name );
		$value        = qtn_set_language_value( $strings, $data['name'] );
		$data['name'] = qtn_ml_value_to_string( $value );

		$this->description = array(
			'old' => $old_value->description,
			'new' => $args['description']
		);

		return $data;
	}

	public function update_description( $tt_id, $taxonomy ) {
		global $wpdb, $qtn_config;
		if ( ! in_array( $taxonomy, $qtn_config->settings['taxonomies'] ) ) {
			return;
		}

		if ( ! $this->description ) {
			return;
		}

		$value = $this->description['new'];

		if ( qtn_is_ml_value( $value ) ) {
			return;
		}

		$old_value   = $this->description['old'];
		$strings     = qtn_value_to_ml_array( $old_value );
		$value       = qtn_set_language_value( $strings, $value );
		$description = qtn_ml_value_to_string( $value );

		$wpdb->update( $wpdb->term_taxonomy, compact( 'description' ), array( 'term_taxonomy_id' => $tt_id ) );
	}
}
