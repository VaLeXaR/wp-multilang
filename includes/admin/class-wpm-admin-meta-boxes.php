<?php
/**
 * WPM Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types).
 *
 * @category Admin
 * @package  WPM/Includes/Admin
 * @version  1.0.1
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Admin_Meta_Boxes.
 */
class WPM_Admin_Meta_Boxes {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var boolean
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 *
	 * @var array
	 */
	public static $meta_box_errors = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		add_action( 'wpm_process_meta', __NAMESPACE__ . '\Meta_Boxes\WPM_Meta_Box_Post_Languages::save' );

		// Save Comment Meta Boxes.
		add_filter( 'comment_edit_redirect', __NAMESPACE__ . '\Meta_Boxes\WPM_Meta_Box_Comment_Languages::save', 1, 2 );

		// Error handling (for showing errors from meta boxes on next page load)
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Add an error message.
	 *
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'wpm_meta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = maybe_unserialize( get_option( 'wpm_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {

			echo '<div id="wpm_errors" class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '</div>';

			// Clear
			delete_option( 'wpm_meta_box_errors' );
		}
	}

	/**
	 * Add WPM Meta boxes.
	 *
	 * @param string $post_type
	 */
	public function add_meta_boxes( $post_type ) {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ( 'attachment' !== $post_type ) && null !== wpm_get_post_config( $post_type ) ) {
			add_meta_box( "wpm-{$post_type}-languages", __( 'Languages', 'wp-multilang' ), __NAMESPACE__ . '\Meta_Boxes\WPM_Meta_Box_Post_Languages::output', $post_type, 'side' );
		}

		// Comment languages.
		if ( 'comment' === $screen_id && isset( $_GET['c'] ) ) {
			add_meta_box( 'wpm-comment-languages', __( 'Languages', 'wp-multilang' ), __NAMESPACE__ . '\Meta_Boxes\WPM_Meta_Box_Comment_Languages::output', 'comment', 'normal' );
		}
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param  int      $post_id
	 * @param  \WP_Post $post
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// $post_id and $post are required
		if ( empty( $post_id ) || null === $post || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce
		if ( empty( $_POST['wpm_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wpm_meta_nonce'], 'wpm_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || (int) $_POST['post_ID'] !== $post_id ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		self::$saved_meta_boxes = true;

		do_action( 'wpm_process_meta', $post_id, $post );
		do_action( 'wpm_process_' . $post->post_type . '_meta', $post_id, $post );
	}
}
