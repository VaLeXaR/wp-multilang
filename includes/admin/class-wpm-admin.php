<?php
/**
 * WPM Admin
 *
 * @class      WPM_Admin
 * @category   Admin
 * @package    WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Admin class.
 */
class WPM_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_init', array( $this, 'set_edit_lang' ) );
		add_action( 'admin_footer', 'wpm_print_js', 25 );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once __DIR__ . '/wpm-admin-functions.php';

		new WPM_Admin_Menus();
		new WPM_Admin_Edit_Menus();
		new WPM_Admin_Customizer();
		if ( ! WPM_Admin_Notices::get_notices() ) {
			WPM_Admin_Notices::init();
		}
		new WPM_Admin_Posts();
		new WPM_Admin_Taxonomies();
		new WPM_Admin_Settings();
		new WPM_Admin_Widgets();
		new WPM_Admin_Assets();
		new WPM_Admin_Qtranslate();
		new WPM_Admin_Gutenberg();
	}

	/**
	 * Set edit lang
	 */
	public function set_edit_lang() {
		$user_id = get_current_user_id();

		if ( isset( $_GET['edit_lang'] ) || ! get_user_meta( $user_id, 'edit_lang', true ) ) {
			update_user_meta( $user_id, 'edit_lang', wpm_get_language() );
		}
	}

	/**
	 * Change the admin footer text on WP Multilang settings pages.
	 *
	 * @since  2.1.2
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_translations' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();

		// Check to make sure we're on a WP Multilang settings page.
		if ( ! empty( $current_screen ) && ( 'settings_page_wpm-settings' === $current_screen->id ) ) {
			// Change the footer text
			if ( ! get_option( 'wpm_admin_footer_text_rated' ) ) {
				$footer_text = sprintf(
					/* translators: 1: WP Multilang 2:: five stars */
					__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'wp-multilang' ),
					sprintf( '<strong>%s</strong>', esc_html__( 'WP Multilang', 'wp-multilang' ) ),
					'<a href="https://wordpress.org/support/plugin/wp-multilang/reviews?rate=5#new-post" target="_blank" class="wpm-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wp-multilang' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
				wpm_enqueue_js( "
					$('a.wpm-rating-link').click( function() {
						$.post('" . wpm()->ajax_url() . "', {action: 'wpm_rated'});
						$(this).parent().text($(this).data('rated'));
					});
				" );
			} else {
				$footer_text = __( 'Thank you for translating with WP Multilang.', 'wp-multilang' );
			}
		}

		return $footer_text;
	}
}
