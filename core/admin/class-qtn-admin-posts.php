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
			add_action( 'admin_init', array($this, 'init'));
			add_filter( 'wp_insert_post_data', array( $this, 'save_post' ), 0, 2 );
			add_filter( 'wp_insert_attachment_data', array( $this, 'save_post' ), 0, 2 );
			add_filter( 'redirect_post_location', array( $this, 'redirect_after_save' ), 0 );
			add_filter( 'get_sample_permalink', array( $this, 'translate_post_link' ), 0);
			add_filter( 'preview_post_link', array( $this, 'translate_post_link' ), 0);
		}


		public function init() {
			global $qtn_config;

			foreach($qtn_config->settings['post_types'] as $post_type) {

				if ( 'attachment' == $post_type) {
					add_filter( "manage_media_columns", array( $this, 'language_columns' ) );
					add_action( "manage_media_custom_column", array( $this, 'render_language_column' ) );
					continue;
				}

				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'language_columns' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_language_column' ) );
			}

		}


		public function translate_post() {
			global $post, $qtn_config;

			$screen = get_current_screen();

			if ( 'add' === $screen->action) {
				return;
			}

			$languages = $qtn_config->languages;
			$lang      = isset( $_GET['edit_lang'] ) ? qtn_clean( $_GET['edit_lang'] ) : $qtn_config->languages[ get_locale() ];
			$post      = qtn_translate_object( $post );
			?>
			<input type="hidden" name="lang" value="<?php echo $lang; ?>">
			<?php

			if ( count( $languages ) <= 1 ) {
				return;
			}

			if ( in_array( $post->post_type, $qtn_config->settings['post_types'] ) ) {

				$url = remove_query_arg( 'edit_lang', get_edit_post_link( $post->ID ) );
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
		}


		public function save_post( $data, $postarr ) {
			global $qtn_config;

			if ( ! in_array( $data['post_type'], $qtn_config->settings['post_types'] ) ) {
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
					case 'title':
					case 'attr_title':
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

			if ( empty( $data['post_name'] ) ) {
				$data['post_name'] = sanitize_title( qtn_translate_value( $data['post_title'], $qtn_config->languages[ $qtn_config->default_locale ] ) );
			}

			return $data;
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

				$post   = qtn_untranslate_post( get_post() );
				$output  = array();
				$text    = $post->post_title . $post->post_content;
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
					if ( is_array( $link ) ) {
						$link[0] = str_replace( home_url(), home_url('/' . $lang), $link[0] );
					} else {
						$link = str_replace( home_url(), home_url('/' . $lang ), $link );

					}
				}
			}

			return $link;
		}
	}

endif;
