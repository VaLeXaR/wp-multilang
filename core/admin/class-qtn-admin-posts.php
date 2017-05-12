<?php
/**
 * Post Types Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QtN_Admin_Posts' ) ) :

	/**
	 * WC_Admin_Post_Types Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class QtN_Admin_Posts {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'edit_form_top', array( $this, 'translate_post' ), 0 );
			add_action( 'admin_init', array( $this, 'init' ) );
			add_filter( 'wp_insert_post_data', array( $this, 'save_post' ), 0, 2 );
			add_filter( 'wp_insert_attachment_data', array( $this, 'save_post' ), 0, 2 );
			add_filter( 'get_sample_permalink', array( $this, 'translate_post_link' ), 0 );
			add_filter( 'preview_post_link', array( $this, 'translate_post_link' ), 0 );
		}


		public function init() {

			$settings = qtn_get_settings();

			foreach ( $settings['post_types'] as $post_type ) {

				if ( 'attachment' == $post_type ) {
					add_filter( "manage_media_columns", array( $this, 'language_columns' ) );
					add_action( "manage_media_custom_column", array( $this, 'render_language_column' ) );
					continue;
				}

				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'language_columns' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_language_column' ) );
			}

		}


		public function translate_post() {
			global $post;
			$post = qtn_translate_object( $post );
			get_permalink();
		}


		public function save_post( $data, $postarr ) {
			$settings = qtn_get_settings();

			if ( ! in_array( $data['post_type'], $settings['post_types'] ) ) {
				return $data;
			}

			if ( 'attachment' !== $data['post_type'] ) {

				if ( 'trash' == $postarr['post_status'] ) {
					return $data;
				}

				if ( isset( $_GET['action'] ) && 'untrash' == $_GET['action'] ) {
					return $data;
				}
			}

			$post_id = isset( $data['ID'] ) ? qtn_clean( $data['ID'] ) : ( isset( $postarr['ID'] ) ? qtn_clean( $postarr['ID'] ) : 0 );

			if ( ! $post_id ) {
				return $data;
			}

			foreach ( $data as $key => $content ) {
				switch ( $key ) {
					case 'post_title':
					case 'post_content':
					case 'post_excerpt':
						if ( qtn_is_ml_value( $content ) ) {
							break;
						}

						$old_value    = get_post_field( $key, $post_id, 'edit' );
						$strings      = qtn_value_to_ml_array( $old_value );
						$value        = qtn_set_language_value( $strings, $data[ $key ] );
						$data[ $key ] = qtn_ml_value_to_string( $value );
						break;
				}
			}

			if ( 'nav_menu_item' == $data['post_type'] ) {
				$screen = get_current_screen();

				if ( 'POST' == $_SERVER['REQUEST_METHOD'] && 'update' == $_POST['action'] && $screen->id == 'nav-menus' ) {
					// hack to get wp to create a post object when too many properties are empty
					if ( '' == $data['post_title'] && '' == $data['post_content'] ) {
						$data['post_content'] = ' ';
					}
				}
			}

			$languages      = qtn_get_languages();
			$default_locale = qtn_get_default_locale();

			if ( empty( $data['post_name'] ) ) {
				$data['post_name'] = sanitize_title( qtn_translate_value( $data['post_title'], $languages[ $default_locale ] ) );
			}

			return $data;
		}

		/**
		 * Define custom columns for post_types.
		 *
		 * @param  array $existing_columns
		 *
		 * @return array
		 */
		public function language_columns( $columns ) {
			if ( empty( $columns ) && ! is_array( $columns ) ) {
				$columns = array();
			}

			$insert_after = 'title';

			$i = 0;
			foreach ( $columns as $key => $value ) {
				if ( $key == $insert_after ) {
					break;
				}
				$i ++;
			}

			$columns =
				array_slice( $columns, 0, $i + 1 ) + array( 'languages' => __( 'Languages', 'qtranslate-next' ) ) + array_slice( $columns, $i + 1 );

			return $columns;
		}

		/**
		 * Ouput custom columns for products.
		 *
		 * @param string $column
		 */
		public function render_language_column( $column ) {

			if ( 'languages' == $column ) {

				$post      = qtn_untranslate_post( get_post() );
				$output    = array();
				$text      = $post->post_title . $post->post_content;
				$strings   = qtn_value_to_ml_array( $text );
				$options   = qtn_get_options();
				$languages = qtn_get_languages();

				foreach ( $languages as $locale => $language ) {
					if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
						$output[] = '<img src="' . QN()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
					}
				}

				if ( ! empty( $output ) ) {
					echo implode( '<br />', $output );
				}
			}
		}


		public function translate_post_link( $link ) {
			$languages = qtn_get_languages();
			$lang = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : qtn_clean( $_COOKIE['edit_language'] );
			if ( in_array( $lang, $languages ) && $lang != $languages[ qtn_get_default_locale() ] ) {
				if ( is_array( $link ) ) {
					$link[0] = str_replace( home_url(), home_url( '/' . $lang ), $link[0] );
				} else {
					$link = str_replace( home_url(), home_url( '/' . $lang ), $link );

				}
			}

			return $link;
		}
	}

endif;
