<?php
/**
 * Translate Post Types in Admin
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 * @version  1.0.3
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Admin_Posts Class.
 *
 * Handles the edit posts views and some functionality on the edit post screen for posts.
 */
class WPM_Admin_Posts {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_lang_indicator' ) );
		add_filter( 'page_link', array( $this, 'translate_post_link' ) );
		add_filter( 'attachment_link', array( $this, 'translate_post_link' ) );
		add_filter( 'post_link', array( $this, 'translate_post_link' ) );
		add_filter( 'post_type_link', array( $this, 'translate_post_link' ) );
		new WPM_Admin_Meta_Boxes();
	}


	/**
	 * Add language column to post type list
	 */
	public function init() {

		$post_types = get_post_types();

		foreach ( $post_types as $post_type ) {

			if ( null === wpm_get_post_config( $post_type ) ) {
				continue;
			}

			if ( 'attachment' === $post_type ) {
				add_filter( 'manage_media_columns', array( $this, 'language_columns' ) );
				add_action( 'manage_media_custom_column', array( $this, 'render_language_column' ) );
				continue;
			}

			add_filter( "manage_{$post_type}_posts_columns", array( $this, 'language_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'render_language_column' ) );
		}
	}


	/**
	 * Define language columns for post_types.
	 *
	 * @param  array $columns
	 *
	 * @return array
	 */
	public function language_columns( $columns ) {
		if ( empty( $columns ) && ! is_array( $columns ) ) {
			$columns = array();
		}

		if ( isset( $columns['languages'] ) ) {
			return $columns;
		}

		$language = array( 'languages' => __( 'Languages', 'wp-multilang' ) );

		if ( isset( $columns['title'] ) ) {
			return wpm_array_insert_after( $columns, 'title', $language );
		}

		if ( isset( $columns['name'] ) ) {
			return wpm_array_insert_after( $columns, 'name', $language );
		}

		$columns = array_merge( $columns, $language );

		return $columns;
	}


	/**
	 * Output language columns for post types.
	 *
	 * @param string $column
	 */
	public function render_language_column( $column ) {

		if ( 'languages' === $column ) {

			$post      = wpm_untranslate_post( get_post() );
			$output    = array();
			$text      = $post->post_title . $post->post_content;
			$strings   = wpm_value_to_ml_array( $text );
			$languages = wpm_get_lang_option();

			foreach ( $languages as $code => $language ) {
				if ( isset( $strings[ $code ] ) && ! empty( $strings[ $code ] ) ) {
					$output[] = '<img src="' . esc_url( wpm_get_flag_url( $language['flag'] ) ) . '" alt="' . $language['name'] . '" title="' . $language['name'] . '">';
				}
			}

			if ( ! empty( $output ) ) {
				echo implode( ' ', $output );
			}
		}
	}


	/**
	 * Add indicator for editing post
	 *
	 * @param \WP_Post $post
	 */
	public function add_lang_indicator( $post ) {

		if ( null === wpm_get_post_config( $post->post_type ) && ( wpm_is_ml_string( $post->post_title ) || wpm_is_ml_value( $post->post_content ) ) ) {

			$languages = wpm_get_languages();
			$language  = wpm_get_language();

			?>
			<div class="misc-pub-section language">
				<?php esc_html_e( 'Current edit language:', 'wp-multilang' ); ?>
				<?php if ( $languages[ $language ]['flag'] ) { ?>
					<img src="<?php echo esc_url( wpm_get_flag_url( $languages[ $language ]['flag'] ) ); ?>" alt="<?php esc_attr_e( $languages[ $language ]['name'] ) ; ?>">
				<?php } else { ?>
					<b><?php esc_html_e( $languages[ $language ]['name'] ) ; ?></b>
				<?php } ?>
			</div>
			<?php
		}
	}

	/**
	 * Translate posts link
	 *
	 * @param $permalink
	 *
	 * @return string
	 */
	public function translate_post_link( $permalink ) {
		return wpm_translate_url( $permalink, wpm_get_language() );
	}
}
