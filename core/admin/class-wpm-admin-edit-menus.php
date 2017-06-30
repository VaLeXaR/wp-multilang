<?php
/**
 * Setup menus in WP admin.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPM/Core/Admin
 * @version  1.0
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Edit_Menus {

	/**
	 * WPM_Admin_Menus constructor.
	 */
	public function __construct() {
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'filter_walker' ) );
		add_filter( 'manage_nav-menus_columns', array( $this, 'nav_menu_manage_columns' ), 11 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item_action' ), 10, 2 );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ) );
	}


	/**
	 * Replace default menu editor walker with ours
	 *
	 * We don't actually replace the default walker. We're still using it and
	 * only injecting some HTMLs.
	 *
	 * @since   0.1.0
	 * @access  private
	 * @wp_hook filter wp_edit_nav_menu_walker
	 * @return  string Walker class name
	 */
	public static function filter_walker() {
		return 'WPM\Core\Libraries\WPM_Walker_Nav_Menu_Edit';
	}

	/**
	 * Adding images as screen options.
	 *
	 * If not checked screen option 'image', uploading form not showed.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function nav_menu_manage_columns( $columns ) {
		$columns['language'] = __( 'Language', 'wpm' );

		return $columns;
	}


	/**
	 * Save custom field value
	 *
	 * @wp_hook action wp_update_nav_menu_item
	 *
	 * @param int   $menu_id         Nav menu ID
	 * @param int   $menu_item_db_id Menu item ID
	 * @param array $menu_item_args  Menu item data
	 */
	public static function wp_update_nav_menu_item_action( $menu_id, $menu_item_db_id ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );


		$key = 'menu-item-languages';

		// Sanitize
		if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
			// Do some checks here...
			$value = $_POST[ $key ][ $menu_item_db_id ];
		} else {
			$value = null;
		}

		// Update
		if ( ! is_null( $value ) ) {
			update_post_meta( $menu_item_db_id, '_' . str_replace( '-', '_', $key ), $value );
		} else {
			delete_post_meta( $menu_item_db_id, '_' . str_replace( '-', '_', $key ) );
		}
	}

	/**
	 * Add custom fields to menu item.
	 *
	 * @param int    $item_id
	 *
	 * @see http://web.archive.org/web/20141021012233/http://shazdeh.me/2014/06/25/custom-fields-nav-menu-items
	 * @see https://core.trac.wordpress.org/ticket/18584
	 */
	public function menu_item_custom_fields( $item_id ) {

		$_key  = 'languages';
		$key   = sprintf( 'menu-item-%s', $_key );
		$id    = sprintf( 'edit-%s-%s', $key, $item_id );
		$name  = sprintf( '%s[%s]', $key, $item_id );
		$value = get_post_meta( $item_id, '_' . str_replace( '-', '_', $key ), true );

		if ( ! is_array( $value ) ) {
			$value = array();
		}

		$class = sprintf( 'field-%s', $_key );
		$languages = wpm_get_options();
		?>
		<p class="description description-wide <?php echo esc_attr( $class ) ?>">
			<?php _e( 'Show item only in:', 'wpm' ); ?><br>
			<?php foreach ( $languages as $key => $language ) { ?>
			<label><input type="checkbox" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $key ); ?>]" id="<?php esc_attr_e( $id . '_' . $language['slug'] ); ?>" value="<?php esc_attr_e( $language['slug'] ); ?>"<?php if ( in_array( $language['slug'], $value ) ) { ?> checked="checked"<?php } ?>><?php echo $language['name']; ?></label>&emsp;
			<?php } ?>
		</p>
		<?php
	}
}
