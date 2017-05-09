<?php
namespace QtNext\Core;

class QtN_Taxonomies extends \QtN_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';

	public function __construct() {

		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );
		add_action( 'init', array($this, 'init') );

//		add_filter( 'wp_get_object_terms', array($this, 'filter_terms'), 0 );
	}

	public function init() {
		global $qtn_config;

		foreach ( $qtn_config->settings['taxonomies'] as $taxonomy ) {
			add_filter( "get_{$taxonomy}", 'qtn_translate_object', 0 );
		}
	}

	public function filter_terms( $terms ) {
		return array_map( 'qtn_translate_object', $terms);
	}

	public function filter_term( $term ) {
		d($term);
		return $term;
	}
}
