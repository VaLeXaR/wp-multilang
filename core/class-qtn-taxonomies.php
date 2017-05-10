<?php
namespace QtNext\Core;

class QtN_Taxonomies extends \QtN_Object {

	public $object_type = 'term';
	public $object_table = 'termmeta';

	public function __construct() {

		add_filter( 'get_term', 'qtn_translate_object', 0 );
//		add_filter( 'single_term_title', 'qtn_localize_text', 0);
//		add_filter( 'get_the_archive_title', 'qtn_localize_text', 0);
//		add_filter( 'get_the_archive_description', 'qtn_localize_text', 0);
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 0, 5 );


//		add_filter( 'term_name', array($this, 'translate_term_field'), 0, 4);
//		add_filter( 'term_description', array($this, 'translate_term_field'), 0, 4);
	}

	public function translate_term_field( $value, $term_id, $taxonomy, $context ) {

		if ( 'display' == $context ) {
			$value = qtn_translate_value( $value );
		}

		return $value;
	}
}
