<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Taxonomies extends \WPM_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';

	public function __construct() {

		add_filter( 'get_term', 'wpm_translate_object', 0 );
		add_filter( 'get_terms', array($this, 'filter_terms'), 0 );
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );
	}

	public function filter_terms( $terms ) {

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
}
