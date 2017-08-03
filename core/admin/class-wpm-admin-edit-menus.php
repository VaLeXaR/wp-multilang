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
		add_filter( 'manage_nav-menus_columns', array( $this, 'nav_menu_manage_columns' ), 11 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'wp_update_nav_menu_item_action' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_menu_item' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ), 15, 2 );
	}

	/**
	 * Adding languages as screen options.
	 *
	 * If not checked screen option 'languages', uploading form not showed.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function nav_menu_manage_columns( $columns ) {
		$columns['languages'] = __( 'Languages', 'wpm' );

		return $columns;
	}


	/**
	 * Save custom field value
	 *
	 * @wp_hook action wp_update_nav_menu_item
	 *
	 * @param int   $menu_id         Nav menu ID
	 * @param int   $menu_item_db_id Menu item ID
	 */
	public static function wp_update_nav_menu_item_action( $menu_id, $menu_item_db_id ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );


		$key = 'languages';

		// Sanitize
		if ( ! empty( $_POST[ $key ][ $menu_item_db_id ] ) ) {
			// Do some checks here...
			$value = wpm_clean( $_POST[ $key ][ $menu_item_db_id ] );
		} else {
			$value = null;
		}

		// Update
		if ( ! is_null( $value ) ) {
			update_post_meta( $menu_item_db_id, '_' . $key, $value );
		} else {
			delete_post_meta( $menu_item_db_id, '_' . $key );
		}
	}


	/**
	 * Load menu languages meta for each menu item.
	 *
	 * @param $item
	 *
	 * @return mixed
	 */
	public function setup_nav_menu_item( $item ) {
		if ( !isset( $item->languages ) ) {
			$languages = get_post_meta( $item->ID, '_languages', true );
			$item->languages = is_array( $languages ) ? $languages : array();
		}

		return $item;
	}

	/**
	 * Add custom fields to menu item.
	 *
	 * @param int $item_id
	 * @param object $item
	 *
	 * @see http://web.archive.org/web/20141021012233/http://shazdeh.me/2014/06/25/custom-fields-nav-menu-items
	 * @see https://core.trac.wordpress.org/ticket/18584
	 */
	public function menu_item_custom_fields( $item_id, $item ) {

		$_key  = 'languages';
		$key   = sprintf( 'menu-item-%s', $_key );
		$id    = sprintf( 'edit-%s-%s', $key, $item_id );
		$name  = sprintf( '%s[%s]', $_key, $item_id );
		$value = $item->languages;

		$class = sprintf( 'field-%s', $_key );
		$languages = wpm_get_options();
		$i = 0;
		?>
		<p class="description description-wide <?php echo esc_attr( $class ) ?>">
			<?php _e( 'Show item only in:', 'wpm' ); ?><br>
			<?php foreach ( $languages as $language ) { if ( ! $language['enable'] ) continue; ?>
			<label><input type="checkbox" name="<?php esc_attr_e( $name ); ?>[<?php esc_attr_e( $i ); ?>]" id="<?php echo $id . '-' . $language['slug']; ?>" value="<?php esc_attr_e( $language['slug'] ); ?>"<?php if ( in_array( $language['slug'], $value ) ) { ?> checked="checked"<?php } ?>><?php echo $language['name']; ?></label><br>
			<?php $i++; } ?>
		</p>
		<?php
	}
}
