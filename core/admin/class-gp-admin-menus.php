<?php
/**
 * Setup menus in WP admin.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  GamePortal/Admin
 * @version  1.0.0
 */

namespace GP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GP_Admin_Menus' ) ) :

/**
 * GP_Admin_Menus Class.
 */
class GP_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'reports_menu' ), 20 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );

		add_action( 'admin_head', array( $this, 'menu_order_count' ) );
		add_filter( 'custom_menu_order', array( $this, 'custom_menu_order' ) );

		// Add endpoints custom URLs in Appearance > Menus > Pages
//		add_action( 'admin_init', array( $this, 'add_nav_menu_meta_boxes' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menus' ), 31 );
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		global $menu;

		if ( current_user_can( 'manage_game_portal' ) ) {
			$menu[] = array( '', 'read', 'separator-game-portal', '', 'wp-menu-separator game-portal' );
		}

		add_menu_page( __( 'Game Portal', 'game-portal' ), __( 'Game Portal', 'game-portal' ), 'manage_game_portal', 'game-portal', null, 'dashicons-awards', '55.5' );
	}

	/**
	 * Add menu item.
	 */
	public function reports_menu() {
		add_submenu_page( 'game-portal', __( 'Reports', 'game-portal' ),  __( 'Reports', 'game-portal' ) , 'view_game_portal_reports', 'gp-reports', null );
	}

	/**
	 * Add menu item.
	 */
	public function settings_menu() {
		add_submenu_page( 'game-portal', __( 'Game Portal Settings', 'game-portal' ),  __( 'Settings', 'game-portal' ) , 'manage_game_portal', 'gp-settings', array( $this, 'settings_page' ) );
	}

	/**
	 * Adds the order processing count to the menu.
	 */
	public function menu_order_count() {
		global $submenu;

		if ( isset( $submenu['game-portal'] ) ) {
			// Remove 'Game Portal' sub menu item
			unset( $submenu['game-portal'][0] );
		}
	}

	/**
	 * Custom menu order.
	 *
	 * @return bool
	 */
	public function custom_menu_order() {
		return current_user_can( 'manage_game_portal' );
	}

	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		GP_Admin_Settings::output();
	}
	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 */
	public function add_nav_menu_meta_boxes() {
		add_meta_box( 'game_portal_endpoints_nav_link', __( 'GamePortal Endpoints', 'game_portal' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Output menu links.
	 */
	public function nav_menu_links() {
		$exclude = array('share-game', 'purchase-game', 'edit-game', 'edit-level', 'copy-game', 'delete-game');
		?>
		<div id="posttype-game-portal-endpoints" class="posttypediv">
			<div id="tabs-panel-game-portal-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="game-portal-endpoints-checklist" class="categorychecklist form-no-clear">
					<?php
					$i = -1;
					foreach ( GP()->query->query_vars as $key => $value ) {
						if ( in_array( $key, $exclude ) ) {
							continue;
						}
						?>
						<li>
							<label class="menu-item-title">
								<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-object-id]" value="<?php echo esc_attr( $i ); ?>" /> <?php echo esc_html( $key ); ?>
							</label>
							<input type="hidden" class="menu-item-type" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-type]" value="custom" />
							<input type="hidden" class="menu-item-title" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-title]" value="<?php echo esc_html( $key ); ?>" />
							<input type="hidden" class="menu-item-url" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-url]" value="<?php echo esc_url( gp_get_endpoint_url( $key, '', gp_get_page_permalink( 'myaccount' ) ) ); ?>" />
							<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo esc_attr( $i ); ?>][menu-item-classes]" />
						</li>
						<?php
						$i --;
					}
					?>
				</ul>
			</div>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#posttype-game-portal-endpoints' ); ?>" class="select-all"><?php _e( 'Select All', 'game-portal' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'game-portal' ); ?>" name="add-post-type-menu-item" id="submit-posttype-game-portal-endpoints">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Add the "Visit Game Portal" link in admin bar main menu.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar_menus( $wp_admin_bar ) {
		if ( ! is_admin() || ! is_user_logged_in() ) {
			return;
		}

		// Show only when the user is a member of this site, or they're a super admin.
		if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
			return;
		}

		// Don't display when shop page is the same of the page on front.
		if ( get_option( 'page_on_front' ) == gp_get_page_id( 'games' ) ) {
			return;
		}

		// Add an option to visit the store.
		$wp_admin_bar->add_node( array(
			'parent' => 'site-name',
			'id'     => 'view-store',
			'title'  => __( 'Visit Game Portal', 'game-portal' ),
			'href'   => gp_get_page_permalink( 'games' )
		) );
	}
}

endif;
