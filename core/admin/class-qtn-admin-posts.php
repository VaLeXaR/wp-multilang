<?php
/**
 * Post Types Admin
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  GamePortal/Admin
 */

namespace QtNext\Core\Admin;

use GP;

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

			add_action( 'admin_init', array( $this, 'save_post' ), 0 );

			add_filter( 'redirect_post_location', array( $this, 'redirect_after_save' ), 0 );

			// WP List table columns. Defined here so they are always available for events such as inline editing.
//			add_filter( 'manage_wpsc_cart_orders_posts_columns', array( $this, 'orders_columns' ) );
//
//			add_action( 'manage_wpsc_cart_orders_posts_custom_column', array( $this, 'render_order_columns' ), 10, 2 );
//
//			add_filter( 'bulk_actions-edit-wpsc_cart_orders', array( $this, 'order_bulk_actions' ) );

			// Bulk / quick edit
//			add_filter( 'handle_bulk_actions-edit-wpsc_cart_orders', array( $this, 'order_bulk_download' ), 10, 3 );

			// Status transitions
//			add_action( 'before_delete_post', array( $this, 'delete_order_invoices' ) );

			// Meta-Box Class
//			new GP_Admin_Meta_Boxes();

			// Disable post type view mode options
//			add_filter( 'view_mode_post_types', array( $this, 'disable_view_mode_options' ) );
//
//			add_action( 'restrict_manage_posts', array( $this, 'add_filter_media_author' ) );
//
//			add_action( 'parse_query', array( $this, 'search_user_media' ) );
		}


		public function translate_post() {
			global $post, $qtn_config, $locale;
			$languages = $qtn_config->languages;
			$lang      = $qtn_config->languages[$locale];
			$post = qtn_translate_post( $post );

			if ( isset( $_GET['edit_lang'] ) ) {
				$lang = qtn_clean( $_GET['edit_lang'] );

				if ( in_array( $lang, $languages ) ) {
					foreach ( $languages as $key => $language ) {
						if ( $language == $lang ) {
							$post = qtn_translate_post( $post, $key );
						}
					}
				}
			}

			//TODO додати фільтр на тип запису

			$url = remove_query_arg( 'edit_lang', get_edit_post_link( $post->ID ) );

			?>
			<ul class="language-switcher">
				<?php foreach ( $languages as $key => $language ) { ?>
					<li<?php if ( $lang == $language ) { ?> class="active"<?php } ?>><a
							href="<?php echo add_query_arg( 'edit_lang', $language, $url ); ?>"><?php echo $qtn_config->options[ $key ]['name']; ?></a>
					</li>
				<?php } ?>
			</ul>
			<input type="hidden" name="edit_lang" value="<?php echo $lang; ?>">
			<?php
		}

		public function save_post() {

			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

				if ( isset( $_POST['action'] ) && ( 'editpost' == $_POST['action'] ) && isset( $_POST['edit_lang'] ) ) {
					$post_id = qtn_clean( $_POST['post_ID'] );
					$lang    = qtn_clean( $_POST['edit_lang'] );

					$post_fields = array(
						'post_title' => 'post_title',
						'content'    => 'post_content',
						'excerpt'    => 'post_excerpt'
					);

					foreach ( $post_fields as $field => $post_field ) {
						if ( isset( $_POST[ $field ] ) ) {
							$old_value        = get_post_field( $post_field, $post_id, 'edit' );
							$strings          = qtn_string_to_localize_array( $old_value );
							$value            = $_POST[ $field ];
							$strings[ $lang ] = $value;
							$_POST[ $field ]  = qtn_localize_array_to_string( $strings );
						}
					}
				}
			}
		}

		public function redirect_after_save( $location ) {
			if ( isset( $_POST['edit_lang'] ) ) {
				$location = add_query_arg( 'edit_lang', qtn_clean( $_POST['edit_lang'] ), $location );
			}

			return $location;
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

			$columns['invoice']     = __( 'Invoice', 'game-portal' );
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
					echo '<a href="' . wp_nonce_url( admin_url( '?preview_game_portal_invoice=true&order_id=' . $post_id ), 'preview-invoice' ) . '" class="button">' . __( 'Download', 'game-portal' ) . '</a>';
				} else {
					echo '<button type="button" class="button button-primary gp-generate-invoice" data-id="' . $post_id . '">' . __( 'Generate', 'game-portal' ) . '</button>';
				}
			}
			if ( 'invoice_num' == $column ) {
				echo get_post_meta( $post_id, '_gp_formatted_invoice_number', true );
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

			$actions['download_invoices'] = __( 'Download Invoices', 'game-portal' );

			if ( isset( $actions['edit'] ) ) {
				unset( $actions['edit'] );
			}

			return $actions;
		}
	}

endif;
