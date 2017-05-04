<?php
/**
 * Load assets
 *
 * @author      VaLeXaR
 * @category    Admin
 * @package     GamePortal/Admin
 */

namespace GP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GP_Admin_Assets' ) ) :

/**
 * WC_Admin_Assets Class.
 */
class GP_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_head',            array( $this, 'level_taxonomy_styles' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		global $wp_scripts;

		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';
		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';

		// Register admin styles
		wp_register_style( 'game_portal_admin_menu', gp_asset_path('css/menu.css'), array(), GP_VERSION );
		wp_register_style( 'game_portal_admin', gp_asset_path('css/admin.css'), array(), GP_VERSION );
		wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );
		wp_register_style( 'game_portal_orders', gp_asset_path('css/orders.css'), array(), GP_VERSION );

		// Sitewide menu CSS
		wp_enqueue_style( 'game_portal_admin_menu' );

		// Admin styles for GP pages only
		if ( in_array( $screen_id, gp_get_screen_ids() ) ) {
			wp_enqueue_style( 'game_portal_admin' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_style( 'wp-color-picker' );
		}
	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $wp_query, $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$gp_screen_id = sanitize_title( __( 'Game Portal', 'game-portal' ) );
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts
		wp_register_script( 'game_portal_admin', gp_asset_path('js/admin/game_portal_admin' . $suffix . '.js'), array( 'jquery', 'jquery-blockui', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), GP_VERSION );
		wp_register_script( 'jquery-blockui', gp_asset_path('js/vendor/jquery-blockui/jquery.blockUI' . $suffix . '.js'), array( 'jquery' ), '2.70', true );
		wp_register_script( 'jquery-tiptip', gp_asset_path('js/vendor/jquery-tiptip/jquery.tipTip' . $suffix . '.js'), array( 'jquery' ), GP_VERSION, true );
		wp_register_script( 'zeroclipboard', gp_asset_path('js/vendor/zeroclipboard/jquery.zeroclipboard' . $suffix . '.js'), array( 'jquery' ), GP_VERSION );
		wp_register_script( 'qrcode', gp_asset_path('js/vendor/jquery-qrcode/jquery.qrcode' . $suffix . '.js'), array( 'jquery' ), GP_VERSION );
		wp_register_script( 'select2', gp_asset_path('js/vendor/select2/select2' . $suffix . '.js'), array( 'jquery' ), '3.5.4' );
		wp_register_script( 'gp-enhanced-select', gp_asset_path('js/admin/gp-enhanced-select' . $suffix . '.js'), array( 'jquery', 'select2' ), GP_VERSION );
		wp_register_script( 'orders', gp_asset_path('js/admin/orders' . $suffix . '.js'), array( 'jquery' ), GP_VERSION );

		wp_localize_script( 'gp-enhanced-select', 'gp_enhanced_select_params', array(
			'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'game-portal' ),
			'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'game-portal' ),
			'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'game-portal' ),
			'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'game-portal' ),
			'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'game-portal' ),
			'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'game-portal' ),
			'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'game-portal' ),
			'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'game-portal' ),
			'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'game-portal' ),
			'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'game-portal' ),
			'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'game-portal' ),
			'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'game-portal' ),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'search_players_nonce'    => wp_create_nonce( 'search-players' )
		) );

		// GamePortal admin pages
		if ( in_array( $screen_id, gp_get_screen_ids() ) ) {
			wp_enqueue_script( 'game_portal_admin' );
			wp_enqueue_script( 'gp-enhanced-select' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
		}

		// API settings
		if ( $gp_screen_id . '_page_gp-settings' === $screen_id && isset( $_GET['tab'] ) && 'api' == $_GET['tab'] ) {
			wp_register_script( 'gp-api-keys', gp_asset_path('js/admin/api-keys' . $suffix . '.js'), array( 'jquery', 'game_portal_admin', 'underscore', 'backbone', 'wp-util', 'qrcode', 'zeroclipboard' ), GP_VERSION, true );
			wp_enqueue_script( 'gp-api-keys' );
			wp_localize_script(
				'gp-api-keys',
				'game_portal_admin_api_keys',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'update_api_nonce' => wp_create_nonce( 'update-api-key' ),
					'clipboard_failed' => esc_html__( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'game-portal' ),
				)
			);
		}

		if ( 'edit-wpsc_cart_orders' == $screen_id ) {
			wp_enqueue_script( 'orders' );

			$params = array(
				'post_id'                => isset( $post->ID ) ? $post->ID : '',
				'plugin_url'             => GP()->plugin_url(),
				'ajax_url'               => admin_url( 'admin-ajax.php' ),
				'generate_invoice_nonce' => wp_create_nonce( 'generate-invoice' ),
			);

			wp_localize_script( 'orders', 'game_portal_orders_params', $params );
		}

		if ( 'wpsc_cart_orders' == $screen_id ) {
			wp_enqueue_style( 'game_portal_orders' );
			wp_enqueue_script( 'orders' );
		}
	}

	/**
	 * Admin Head.
	 *
	 * Outputs some styles in the admin <head> to show icons on the game-portal admin pages.
	 */
	public function level_taxonomy_styles() {

		if ( ! current_user_can( 'manage_game_portal' ) ) return;
		?>
		<style type="text/css">
			<?php if ( isset($_GET['taxonomy']) && $_GET['taxonomy']=='game' ) : ?>
				.term-slug-wrap, .inline-edit-col label:nth-child(2), .term-description-wrap { display: none; }
			<?php endif; ?>
		</style>
		<?php
	}
}

endif;
