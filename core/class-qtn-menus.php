<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Menus extends \QtN_Object {

	public function __construct() {
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_item' ), 0 );
	}

	public function translate_menu_item( $menu_item ) {

		if ( 'post_type' == $menu_item->type ) {
			$original_object = get_post( $menu_item->object_id );

			$original_title = apply_filters( 'the_title', $original_object->post_title, $original_object->ID );

			if ( '' === $original_title ) {
				/* translators: %d: ID of a post */
				$original_title = sprintf( __( '#%d (no title)' ), $original_object->ID );
			}

			$menu_item->title = '' == $menu_item->post_title ? $original_title : $menu_item->post_title;

		} elseif ( 'taxonomy' == $menu_item->type ) {
			$object = get_taxonomy( $menu_item->object );
			if ( $object ) {
				$menu_item->type_label = $object->labels->singular_name;
			} else {
				$menu_item->type_label = $menu_item->object;
				$menu_item->_invalid = true;
			}

			$term_url = get_term_link( (int) $menu_item->object_id, $menu_item->object );
			$menu_item->url = !is_wp_error( $term_url ) ? $term_url : '';

			$original_title = get_term_field( 'name', $menu_item->object_id, $menu_item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
			$menu_item->title = '' == $menu_item->post_title ? $original_title : $menu_item->post_title;

		}

//		d($menu_item->title);

		return $menu_item;
	}
}
//nav_menu_attr_title
//wp_get_nav_menu_items
//wp_get_nav_menu_item
//wp_nav_menu_objects
//pre_wp_nav_menu
//wp_nav_menu_args
//the_title
//nav_menu_description
