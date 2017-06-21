<?php
/**
 * Translate WP menus.
 *
 * @author   VaLeXaR
 * @category Class
 * @package  WPM/Core
 * @version  1.0.2
 */

namespace WPM\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_Menus Class.
 */
class WPM_Menus {

	/**
	 * WPM_Menus constructor.
	 */
	public function __construct() {
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_item' ), ( 'POST' === $_SERVER['REQUEST_METHOD'] ? 99 : 0 ) );
		add_filter( 'customize_nav_menu_available_items', array( $this, 'filter_menus' ), 0 );
		add_filter( 'customize_nav_menu_searched_items', array( $this, 'filter_menus' ), 0 );
	}

	/**
	 * Translate title in menu item
	 *
	 * @param $items
	 *
	 * @return mixed
	 */
	public function filter_menus( $items ) {
		foreach ( $items as &$item ) {
			$item['title'] = wpm_translate_string( $item['title'] );
		}

		return $items;
	}


	/**
	 * Translate menu item
	 *
	 * @param $menu_item
	 *
	 * @return mixed
	 */
	public function translate_menu_item( $menu_item ) {
		$menu_item = wpm_translate_object( $menu_item );

		if ( isset( $menu_item->post_type ) ) {
			if ( 'nav_menu_item' === $menu_item->post_type ) {

				if ( 'post_type' === $menu_item->type ) {

					$object = get_post_type_object( $menu_item->object );

					if ( $object ) {
						$menu_item->type_label = $object->labels->singular_name;
					} else {
						$menu_item->type_label = $menu_item->object;
					}

					$original_object = get_post( $menu_item->object_id );

					/** This filter is documented in wp-includes/post-template.php */
					$original_title = apply_filters( 'the_title', $original_object->post_title, $original_object->ID );

					if ( '' === $original_title ) {
						/* translators: %d: ID of a post */
						$original_title = sprintf( __( '#%d (no title)' ), $original_object->ID );
					}

					$menu_item->title = '' === $menu_item->post_title ? $original_title : $menu_item->post_title;

				} elseif ( 'post_type_archive' === $menu_item->type ) {

					$object = get_post_type_object( $menu_item->object );

					if ( $object ) {
						$menu_item->title = '' === $menu_item->post_title ? $object->labels->archives : $menu_item->post_title;
					} else {
						$menu_item->_invalid = true;
					}

					$menu_item->type_label = __( 'Post Type Archive' );
					$menu_item->url        = get_post_type_archive_link( $menu_item->object );

				} elseif ( 'taxonomy' === $menu_item->type ) {

					$object = get_taxonomy( $menu_item->object );

					if ( $object ) {
						$menu_item->type_label = $object->labels->singular_name;
					} else {
						$menu_item->type_label = $menu_item->object;
					}

					$original_title = get_term_field( 'name', $menu_item->object_id, $menu_item->object, 'raw' );

					if ( is_wp_error( $original_title ) ) {
						$original_title = false;
					}

					$menu_item->title = '' === $menu_item->post_title ? $original_title : $menu_item->post_title;

				} else {

					$menu_item->type_label = __( 'Custom Link' );
					$menu_item->title      = $menu_item->post_title;
				}// End if().

				$menu_item->attr_title = ! isset( $menu_item->attr_title ) ? apply_filters( 'nav_menu_attr_title', $menu_item->post_excerpt ) : $menu_item->attr_title;
				if ( ! isset( $menu_item->description ) ) {
					$menu_item->description = apply_filters( 'nav_menu_description', wp_trim_words( $menu_item->post_content, 200 ) );
				}
			} else {

				$object                = get_post_type_object( $menu_item->post_type );
				$menu_item->object     = $object->name;
				$menu_item->type_label = $object->labels->singular_name;

				if ( '' === $menu_item->post_title ) {
					/* translators: %d: ID of a post */
					$menu_item->post_title = sprintf( __( '#%d (no title)' ), $menu_item->ID );
				}

				$menu_item->title = $menu_item->post_title;

				/** This filter is documented in wp-includes/nav-menu.php */
				$menu_item->attr_title = apply_filters( 'nav_menu_attr_title', '' );

				/** This filter is documented in wp-includes/nav-menu.php */
				$menu_item->description = apply_filters( 'nav_menu_description', '' );
			}// End if().
		} elseif ( isset( $menu_item->taxonomy ) ) {

			$object                = get_taxonomy( $menu_item->taxonomy );
			$menu_item->type_label = $object->labels->singular_name;

			$menu_item->title       = $menu_item->name;
			$menu_item->attr_title  = '';
			$menu_item->description = get_term_field( 'description', $menu_item->term_id, $menu_item->taxonomy );

		}// End if().

		$menu_item->title = '' === $menu_item->title ? sprintf( __( '#%d (no title)' ), $menu_item->ID ) : $menu_item->title ;

		return $menu_item;
	}
}
