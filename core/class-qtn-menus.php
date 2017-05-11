<?php

namespace QtNext\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class QtN_Menus {


	public function __construct() {
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_item' ), 0 );
	}

	public function translate_menu_item( $menu_item ) {
		if ($menu_item->ID == 134) {
			d($menu_item->post_title, $menu_item->title);
		}

		$menu_item       = qtn_translate_object( $menu_item );

		if ( 'post_type' == $menu_item->type ) {
			$original_object = get_post( $menu_item->object_id );
			$original_title = apply_filters( 'the_title', $original_object->post_title, $original_object->ID );

			if ( '' === $original_title ) {
				/* translators: %d: ID of a post */
				$original_title = sprintf( __( '#%d (no title)' ), $original_object->ID );
			}

			$menu_item->title = '' == $menu_item->post_title ? $original_title : $menu_item->post_title;
		}

//		die();

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
