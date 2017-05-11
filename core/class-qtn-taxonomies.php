<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Taxonomies extends \QtN_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';

	public function __construct() {

		add_filter( 'get_term', 'qtn_translate_object', 0 );
		add_filter( 'get_terms', array($this, 'filter_terms'), 0 );
		add_filter( 'get_edit_term_link', array( $this, 'edit_term_link' ), 0, 3 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );

//		add_filter( 'single_term_title', 'qtn_translate_string', 0);
//		add_filter( 'get_the_archive_title', 'qtn_translate_string', 0);
//		add_filter( 'get_the_archive_description', 'qtn_translate_string', 0);
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
}
