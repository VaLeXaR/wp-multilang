<?php
/**
 * Taxonomies Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'QtN_Admin_Taxonomies' ) ) :

	/**
	 * QtN_Admin_Taxonomies Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class QtN_Admin_Taxonomies {

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_init', array($this, 'init'));

//			add_filter( 'redirect_post_location', array( $this, 'redirect_after_save' ), 0 );
//			add_filter( 'get_sample_permalink', array( $this, 'translate_post_link' ), 0);
		}


		public function init() {
			global $qtn_config;

			foreach($qtn_config->settings['taxonomies'] as $taxonomy) {

				add_action( "{$taxonomy}_term_edit_form_top", array( $this, 'translate_taxonomies' ), 0 );

/*				if ( 'attachment' == $post_type) {
					add_filter( "manage_media_columns", array( $this, 'language_columns' ) );
					add_action( "manage_media_custom_column", array( $this, 'render_language_column' ) );
					continue;
				}

				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'language_columns' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_language_column' ) );*/
			}

		}


		public function translate_taxonomies($tag) {
			global $qtn_config;

			$languages = $qtn_config->languages;
			$lang      = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : $qtn_config->languages[ get_locale() ];
			$tag      = qtn_translate_object( $tag );
			?>
			<input type="hidden" name="lang" value="<?php echo $lang; ?>">
			<?php

			if ( count( $languages ) <= 1 ) {
				return;
			}

			$url = remove_query_arg( 'edit_lang', get_edit_term_link( $tag->term_id ) );
			?>
			<h3 class="nav-tab-wrapper language-switcher">
				<?php foreach ( $languages as $key => $language ) { ?>
					<a class="nav-tab<?php if ( $lang == $language ) { ?> nav-tab-active<?php } ?>"
					   href="<?php echo add_query_arg( 'edit_lang', $language, $url ); ?>">
						<img src="<?php echo QN()->flag_dir() . $qtn_config->options[ $key ]['flag'] . '.png'; ?>"
						     alt="<?php echo $qtn_config->options[ $key ]['name']; ?>">
						<span><?php echo $qtn_config->options[ $key ]['name']; ?></span>
					</a>
				<?php } ?>
			</h3>
			<?php
		}

		public function redirect_after_save( $location ) {
			if ( isset( $_POST['lang'] ) ) {
				$location = add_query_arg( 'edit_lang', qtn_clean( $_POST['lang'] ), $location );
			}

			return $location;
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
			global $qtn_config;

			if ( 'languages' == $column ) {

				$_post   = qtn_untranslate_post( get_post() );
				$output  = array();
				$text    = $_post->post_title . $_post->post_content;
				$strings = qtn_value_to_ml_array( $text );
				$options = $qtn_config->options;

				foreach ( $qtn_config->languages as $locale => $language ) {
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
			global $qtn_config;
			if ( is_admin() && isset( $_GET['edit_lang'] ) ) {
				$lang      = qtn_clean( $_GET['edit_lang'] );
				if ( in_array( $lang, $qtn_config->languages ) && $lang != $qtn_config->languages[ $qtn_config->default_locale ] ) {
					$link[0] = str_replace( home_url(), home_url() . '/' . $lang, $link[0] );
				}
			}

			return $link;
		}
	}

endif;
