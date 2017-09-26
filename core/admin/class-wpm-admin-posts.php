<?php
/**
 * Translate Post Types in Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPM/Core/Admin
 * @version  1.0.3
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Admin_Post_Types Class.
 *
 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
 */
class WPM_Admin_Posts {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_lang_indicator' ) );
		add_filter( 'preview_post_link', array( $this, 'translate_post_link' ), 0 );
		new WPM_Admin_Meta_Boxes();
	}


	/**
	 * Add language column to post type list
	 */
	public function init() {

		$config       = wpm_get_config();
		$posts_config = $config['post_types'];

		foreach ( $posts_config as $post_type => $post_config ) {

			if ( is_null( $post_config ) ) {
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
	 * Fix for translate post in edit post page
	 * use get_post
	 */
	public function translate_post() {
		global $post;
		$post = wpm_translate_object( $post );
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

		return wpm_array_insert_after( $columns, 'title', array( 'languages' => __( 'Languages', 'wpm' ) ) );
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
			$options   = wpm_get_options();
			$languages = wpm_get_all_languages();

			foreach ( $languages as $locale => $language ) {
				if ( isset( $strings[ $language ] ) && ! empty( $strings[ $language ] ) ) {
					$output[] = '<img src="' . WPM()->flag_dir() . $options[ $locale ]['flag'] . '.png" alt="' . $options[ $locale ]['name'] . '" title="' . $options[ $locale ]['name'] . '">';
				}
			}

			if ( ! empty( $output ) ) {
				echo implode( '<br />', $output );
			}
		}
	}


	/**
	 * Translate preview url for posts
	 *
	 * @param $link
	 *
	 * @return mixed
	 */
	public function translate_post_link( $link ) {
		$languages = wpm_get_languages();
		$lang      = wpm_get_language();
		if ( in_array( $lang, $languages, true ) && $lang !== $languages[ wpm_get_default_locale() ] ) {
			$link = str_replace( home_url(), home_url( '/' . $lang ), $link );
		}

		return $link;
	}


	/**
	 * Add indicator for editing post
	 *
	 * @param \WP_Post $post
	 */
	public function add_lang_indicator( $post ) {
		$options   = wpm_get_options();
		$languages = wpm_get_all_languages();
		$locales   = array_flip( $languages );
		$lang      = wpm_get_language();
		$config    = wpm_get_config();
		if ( is_null( $config['post_types'][ $post->post_type ] ) && ( wpm_is_ml_string( $post->post_title ) || wpm_is_ml_value( $post->post_content ) ) ) {
			?>
			<div class="misc-pub-section language">
				<?php esc_html_e( 'Current edit language:', 'wpm' ); ?>
				<?php if ( $options[ $locales[ $lang ] ]['flag'] ) { ?>
					<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $locales[ $lang ] ]['flag'] . '.png' ); ?>">
				<?php } else { ?>
					<b><?php echo $options[ $locales[ $lang ] ]['name']; ?></b>
				<?php } ?>
			</div>
			<?php
		}
	}
}
