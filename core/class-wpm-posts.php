<?php

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WPM_Posts extends \WPM_Object {

	public $object_type = 'post';
	public $object_table = 'postmeta';

	public function __construct() {
		add_filter( 'the_posts', array( $this, 'filter_posts' ), 0, 2 );
		add_filter( 'the_post', 'wpm_translate_object', 0 );
		add_filter( 'the_title', 'wpm_translate_string', 0);
		add_filter( "get_{$this->object_type}_metadata", array( $this, 'get_meta_field' ), 0, 3 );
		add_filter( "update_{$this->object_type}_metadata", array( $this, 'update_meta_field' ), 99, 5 );

//		add_filter( 'get_pages', array( $this, 'filter_posts' ), 0, 2 );
//		add_filter( 'the_content', 'wpm_translate_string', 0);
//		add_filter( 'get_the_excerpt', 'wpm_translate_string', 0);
//		add_filter( 'single_post_title', 'wpm_translate_string', 0);
//		add_filter( 'post_type_archive_title', 'wpm_translate_string', 0);
//		add_filter( 'sanitize_title', 'wpm_translate_string', 0);
	}


	public function filter_posts( $posts ) {
		return array_map( 'wpm_translate_object', $posts );
	}
}


//apply_filters( 'wpseo_title', wpseo_replace_vars( $this->get_title( $item->ID, $item->post_type ), $item ) )
//apply_filters( 'wpseo_metadesc', wpseo_replace_vars( $this->get_meta_description( $item->ID, $item->post_type ), $item ) )
// apply_filters( 'wpseo_post_content_for_recalculation', $content, $item )
//apply_filters( 'wpseo_do_meta_box_field_' . $key, $content, $meta_value, $esc_form_key, $meta_field_def, $key )
//apply_filters( 'wpseo_term_description_for_recalculation', $description, $item )
//apply_filters( 'wpseo_metakey', trim( $keywords ) );
//apply_filters( 'wpseo_metakeywords', trim( $keywords ) )
//apply_filters( 'wpseo_metadesc', trim( $metadesc ) )
//apply_filters( 'wpseo_sanitize_tax_meta_' . $key, $clean[ $key ], ( isset( $meta_data[ $key ] ) ? $meta_data[ $key ] : null ), ( isset( $old_meta[ $key ] ) ? $old_meta[ $key ] : null ) )
