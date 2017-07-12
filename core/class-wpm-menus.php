<?php
/**
 * Translate WP menus.
 *
 * @author   VaLeXaR
 * @category Class
 * @package  WPM/Core
 * @version  1.1.0
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
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_url' ) );
		add_filter( 'customize_nav_menu_available_items', array( $this, 'filter_menus' ), 0 );
		add_filter( 'customize_nav_menu_searched_items', array( $this, 'filter_menus' ), 0 );
		add_filter( 'wp_nav_menu_items', array( $this, 'add_languages_to_menu' ) );
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

		$menu_item->title = '' === $menu_item->title ? sprintf( __( '#%d (no title)' ), $menu_item->ID ) : $menu_item->title;

		return $menu_item;
	}


	/**
	 * Translation custom menu link
	 *
	 * @param $menu_item
	 *
	 * @return mixed
	 */
	public function translate_menu_url( $menu_item ) {

		if ( ! is_admin() ) {
			if ( 'custom' === $menu_item->object && ! is_admin() ) {
				$menu_item->url = wpm_translate_url( $menu_item->url );
			}
		}

		return $menu_item;
	}

	/**
	 * Add languages to menu
	 *
	 * @param $items
	 *
	 * @return string
	 */
	public function add_languages_to_menu( $items ) {

		$menu_items = explode( "\n", $items );

		foreach ( $menu_items as $key => $item ) {

			if ( strstr( $item, '#wpm-languages' ) ) {

				$doc = new \DOMDocument();
				// start libxml error managent
				// modify state
				$libxml_previous_state = libxml_use_internal_errors( true );
				@$doc->loadHTML( '<?xml encoding="' . strtolower( get_bloginfo( 'charset' ) ) . '" ?>' . $item );
				// handle errors
				libxml_clear_errors();
				// restore
				libxml_use_internal_errors( $libxml_previous_state );
				// end libxml error management

				$list_item   = $doc->getElementsByTagName( 'li' )->item( 0 );
				$list_id     = $list_item->getAttribute( 'id' );
				$menu_id     = preg_replace( '/[^0-9]+/', '', $list_id );
				$link        = $doc->getElementsByTagName( 'a' )->item( 0 );
				$link_text   = $link->textContent;
				$languages   = wpm_get_languages();
				$options     = wpm_get_options();
				$current_url = wpm_get_current_url();
				$new_items   = array();
				$show_type   = get_post_meta( $menu_id, '_menu_item_languages_show', true );

				foreach ( $languages as $locale => $language ) {

					$language_string = '';

					if ( ( ( 'flag' === $show_type ) || ( 'both' === $show_type ) ) && ( $options[ $locale ]['flag'] ) ) {
						$language_string = '<img src="' . esc_url( WPM()->flag_dir() . $options[ $locale ]['flag'] . '.png' ) . '" alt="' . esc_attr( $options[ $locale ]['name'] ) . '">';
					}

					if ( ( 'name' === $show_type ) || ( 'both' === $show_type ) ) {
						$language_string .= '<span>' . esc_attr( $options[ $locale ]['name'] ) . '</span>';
					}

					$new_item    = str_replace( $link_text, $language_string, $item );
					$new_item    = str_replace( $list_id, 'menu-item-language-' . $language, $new_item );
					$new_items[] = str_replace( '#wpm-languages', esc_url( wpm_translate_url( $current_url, $language ) ), $new_item );
				}

				$menu_items = wpm_array_insert_after( $menu_items, $key, $new_items );
				unset( $doc );
				unset( $menu_items[ $key ] );
			}// End if().
		}// End foreach().

		$items = implode( "\n", $menu_items );

		return $items;
	}
}
