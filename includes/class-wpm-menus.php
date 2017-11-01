<?php
/**
 * Translate WP menus.
 *
 * @category Class
 * @package  WPM/Includes
 */

namespace WPM\Includes;

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
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_item' ), ( 'POST' === $_SERVER['REQUEST_METHOD'] ? 99 : 5 ) );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'translate_menu_url' ) );
		add_filter( 'customize_nav_menu_available_items', array( $this, 'filter_menus' ), 5 );
		add_filter( 'customize_nav_menu_searched_items', array( $this, 'filter_menus' ), 5 );
		add_filter( 'wp_nav_menu_items', array( $this, 'add_languages_to_menu' ), 5 );
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

			if ( preg_match( '/^.*href="#wpm-languages".*$/u', $item ) ) {

				$menu_id = 0;

				if ( preg_match( '/<li id=".+?(\d+)"/u', $item, $matches ) ) {
					$menu_id = $matches[1];
				}

				$languages   = wpm_get_languages();
				$options     = wpm_get_options();
				$current_url = wpm_get_current_url();
				$new_items   = array();
				$show_type   = get_post_meta( $menu_id, '_menu_item_languages_show', true );

				foreach ( $languages as $locale => $language ) {

					$language_string = '';
					$current_class = '';

					if ( wpm_get_language() == $language ) {
						$current_class = 'class="active-language"';
					}

					if ( ( ( 'flag' === $show_type ) || ( 'both' === $show_type ) ) && ( $options[ $locale ]['flag'] ) ) {
						$language_string = '<img src="' . esc_url( wpm_get_flag_url( $options[ $locale ]['flag'] ) ) . '" alt="' . esc_attr( $options[ $locale ]['name'] ) . '">';
					}

					if ( ( 'name' === $show_type ) || ( 'both' === $show_type ) ) {
						$language_string .= '<span>' . esc_attr( $options[ $locale ]['name'] ) . '</span>';
					}

					$new_item = preg_replace( '/<a href="[^"]+">[^@]+<\/a>/', '<a href="' . esc_url( wpm_translate_url( $current_url, $language ) ) . '" ' . $current_class . ' data-lang="' . esc_attr( $language ) . '">' . $language_string . '</a>', $item );
					$new_items[] = str_replace( $menu_id, 'language-' . $language, $new_item );
				}

				$menu_items = wpm_array_insert_after( $menu_items, $key, $new_items );
				unset( $menu_items[ $key ] );
			}// End if().
		}// End foreach().

		$items = implode( "\n", $menu_items );

		return $items;
	}
}
