<?php
/**
 * Post Types Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  GamePortal/Admin
 */

namespace GP\Admin;

use GP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GP_Admin_Post_Types' ) ) :

	/**
	 * WC_Admin_Post_Types Class.
	 *
	 * Handles the edit posts views and some functionality on the edit post screen for WC post types.
	 */
	class GP_Admin_Post_Types {

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Disable Auto Save
			add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );

			// WP List table columns. Defined here so they are always available for events such as inline editing.
			add_filter( 'manage_wpsc_cart_orders_posts_columns', array( $this, 'orders_columns' ) );

			add_action( 'manage_wpsc_cart_orders_posts_custom_column', array( $this, 'render_order_columns' ), 10, 2 );

			add_filter( 'bulk_actions-edit-wpsc_cart_orders', array( $this, 'order_bulk_actions' ) );

			// Bulk / quick edit
			add_filter( 'handle_bulk_actions-edit-wpsc_cart_orders', array( $this, 'order_bulk_download' ), 10, 3 );

			// Status transitions
			add_action( 'before_delete_post', array( $this, 'delete_order_invoices' ) );

			// Meta-Box Class
			new GP_Admin_Meta_Boxes();

			// Disable post type view mode options
			add_filter( 'view_mode_post_types', array( $this, 'disable_view_mode_options' ) );

			add_action( 'restrict_manage_posts', array( $this, 'add_filter_media_author' ) );

			add_action( 'parse_query', array( $this, 'search_user_media' ) );
		}

		/**
		 * Define custom columns for products.
		 *
		 * @param  array $existing_columns
		 *
		 * @return array
		 */
		public function orders_columns( $columns ) {
			if ( empty( $columns ) && ! is_array( $columns ) ) {
				$columns = array();
			}

			$columns['invoice'] = __( 'Invoice', 'game-portal' );
			$columns['invoice_num'] = __( 'Invoice Number', 'game-portal' );

			return $columns;

		}

		/**
		 * Ouput custom columns for products.
		 *
		 * @param string $column
		 */
		public function render_order_columns( $column, $post_id ) {
			if ( 'invoice' == $column ) {
				$invoice = new GP\GP_Invoice_Document( $post_id );
				if ( $invoice->exists() ) {
					echo '<a href="' . wp_nonce_url( admin_url( '?preview_game_portal_invoice=true&order_id=' . $post_id ), 'preview-invoice' )  . '" class="button">' . __( 'Download', 'game-portal' ) . '</a>';
				} else {
					echo '<button type="button" class="button button-primary gp-generate-invoice" data-id="' . $post_id . '">' . __( 'Generate', 'game-portal' ) . '</button>';
				}
			}
			if ( 'invoice_num' == $column ) {
				echo get_post_meta( $post_id, '_gp_formatted_invoice_number', true);
			}
		}

		/**
		 * Remove edit from the bulk actions.
		 *
		 * @param array $actions
		 *
		 * @return array
		 */
		public function order_bulk_actions( $actions ) {

			$actions['download_invoices'] = __( 'Download Invoices', 'game-portal');

			if ( isset( $actions['edit'] ) ) {
				unset( $actions['edit'] );
			}

			return $actions;
		}

		/**
		 * Remove edit from the bulk actions.
		 *
		 *
		 *
		 * @return string
		 */
		public function order_bulk_download( $redirect_to, $action, $post_ids ) {

			if ( $action !== 'download_invoices' ) {
				return $redirect_to;
			}

			$file = tempnam("tmp", "zip");

			$zip = new \ZipArchive();
			$zip->open($file, \ZipArchive::OVERWRITE);

			foreach ( $post_ids as $post_id ) {
				$invoice = new GP\GP_Invoice_Document( $post_id );
				if ($invoice->exists()) {
					$zip->addFile($invoice->get_full_path(), basename($invoice->get_full_path()));
				}
			}

			$zip->close();

			ob_start();

			header('Content-Type: application/zip');
			header('Content-Length: ' . filesize($file));
			header('Content-Disposition: attachment; filename="invoices.zip"');
			readfile($file);
			unlink($file);

			$content       = ob_get_clean();
			echo $content;
			exit;
		}

		/**
		 * Disable the auto-save functionality for Orders.
		 */
		public function disable_autosave() {
			global $post;

			if ( $post && 'wpsc_cart_orders' == $post->post_type ) {
				wp_dequeue_script( 'autosave' );
			}
		}

		/**
		 * Removes variations etc belonging to a deleted post, and clears transients.
		 *
		 * @param mixed $id ID of post being deleted
		 */
		public function delete_order_invoices( $id ) {

			$post_type = get_post_type( $id );

			if ( 'wpsc_cart_orders' == $post_type ) {
				$invoice = new GP\GP_Invoice_Document( $id );
				$invoice->delete();
			}
		}

		/**
		 * Removes orders from the list of post types that support "View Mode" switching.
		 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
		 * it, so we want to hide the useless UI from the screen options tab.
		 *
		 * @param  array $post_types Array of post types supporting view mode
		 *
		 * @return array             Array of post types supporting view mode, without products, orders, and coupons
		 */
		public function disable_view_mode_options( $post_types ) {
			unset( $post_types['wpsc_cart_orders'] );

			return $post_types;
		}

		public function add_filter_media_author() {

			$screen = get_current_screen();
			if ( $screen->base !== 'upload' ) {
				return;
			}

			$current_user_id = get_current_user_id();
			$user_id        = ! empty( $_GET['author'] ) ? absint( gp_clean( $_GET['author'] ) ) : $current_user_id;
			$user           = get_user_by( 'id', $user_id );
			$user_string    = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')';

			echo '<input type="hidden" class="gp-player-search" name="author" data-placeholder="' . esc_attr__( 'Search for a user&hellip;', 'game-portal' ) . '" data-selected="' . esc_attr( $user_string ) .'" value="' . esc_attr( $user_id ) .'" data-allow_clear="true" />';
		}


		/**
		 * Filter the media files in admin based on author.
		 *
		 * @param mixed $query
		 */
		public function search_user_media( $query ) {

			if (function_exists( 'get_current_screen')) {

				$screen = get_current_screen();

				if ($screen && ( $screen->base !== 'upload' )) {
					return;
				}

				if ( ! isset( $_GET['author'] ) ) {
					$query->query_vars['author'] = get_current_user_id();
				}
			}
		}
	}

endif;
