<?php
/**
 * Setup menus in WP admin.
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 * @version  1.0
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Edit_Menus {

	/**
	 * WPM_Admin_Edit_Menus constructor.
	 */
	public function __construct() {

		add_filter( 'manage_nav-menus_columns', array( $this, 'nav_menu_manage_columns' ), 11 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item' ), 10, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item_language_params' ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_menu_item' ) );
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'filter_walker' ) );
	}

	/**
	 * Add custom field menu
	 *
	 * @wp_hook filter wp_edit_nav_menu_walker
	 *
	 * @param $class_name
	 *
	 * @return  string Walker class name
	 */
	public function filter_walker( $class_name ) {

		if ( ! has_action('wp_nav_menu_item_custom_fields') ) {
			$class_name = 'WPM\Includes\Libraries\WPM_Walker_Nav_Menu_Edit';
		}

		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_languages_setting_custom_fields' ), 5, 2 );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_custom_fields' ), 15, 2 );

		return $class_name;
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
		$columns['languages'] = __( 'Languages', 'wp-multilang' );

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
	public static function update_nav_menu_item( $menu_id, $menu_item_db_id ) {
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
		if ( null !==  $value ) {
			update_post_meta( $menu_item_db_id, '_' . $key, $value );
		} else {
			delete_post_meta( $menu_item_db_id, '_' . $key );
		}
	}

	/**
	 * Add language settings params to menu item
	 *
	 * @param $menu_id
	 * @param $menu_item_db_id
	 */
	public function update_nav_menu_item_language_params( $menu_id, $menu_item_db_id ) {

		if( 'update' !== $_REQUEST['action'] ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		if ( empty( $_POST['menu-item-url'][ $menu_item_db_id ] ) || '#wpm-languages' !== $_POST['menu-item-url'][ $menu_item_db_id ] ) {
			return;
		}

		update_post_meta( $menu_item_db_id, '_menu_item_languages_type', $_POST['languages_type'][ $menu_item_db_id ] );
		update_post_meta( $menu_item_db_id, '_menu_item_languages_show', $_POST['languages_show'][ $menu_item_db_id ] );
	}


	/**
	 * Load menu languages meta for each menu item.
	 *
	 * @param $item
	 *
	 * @return mixed
	 */
	public function setup_nav_menu_item( $item ) {
		if ( ! isset( $item->languages ) ) {
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

		$_key      = 'languages';
		$key       = sprintf( 'menu-item-%s', $_key );
		$id        = sprintf( 'edit-%s-%s', $key, $item_id );
		$name      = sprintf( '%s[%s]', $_key, $item_id );
		$value     = $item->languages;
		$class     = sprintf( 'field-%s', $_key );
		$languages = wpm_get_lang_option();
		$i         = 0;
		?>
		<p class="description description-wide <?php esc_attr_e( $class ) ?>">
			<?php _e( 'Show item only in:', 'wp-multilang' ); ?><br>
			<?php foreach ( $languages as $code => $language ) { if ( ! $language['enable'] ) continue; ?>
			<label><input type="checkbox" name="<?php esc_attr_e( $name ); ?>[<?php echo esc_attr( $i ); ?>]" id="<?php echo $id . '-' . $code; ?>" value="<?php echo esc_attr( $code ); ?>"<?php checked( in_array( $code, $value ) ); ?>><?php echo esc_attr( $language['name'] ); ?></label><br>
			<?php $i++; } ?>
		</p>
		<?php
	}

	/**
	 * Add custom fields to menu item.
	 *
	 * @param int $item_id
	 * @param object $item
	 */
	public function menu_item_languages_setting_custom_fields( $item_id, $item ) {

		if ( '#wpm-languages' !== $item->url ) {
			return;
		}

		$_key  = 'languages_type';
		$key   = sprintf( 'menu-item-%s', $_key );
		$id    = sprintf( 'edit-%s-%s', $key, $item_id );
		$name  = sprintf( '%s[%s]', $_key, $item_id );
		$value = get_post_meta( $item_id, '_menu_item_languages_type', true );
		$class = sprintf( 'field-%s', $_key );

		$type_options = array(
			'inline'   => __( 'Inline', 'wp-multilang' ),
			'single'   => __( 'Single', 'wp-multilang' ),
			'dropdown' => __( 'Dropdown', 'wp-multilang' ),
		);
		?>
		<p class="description description-wide <?php echo esc_attr( $class ); ?>">
			<label for="<?php esc_attr_e( $id ); ?>"><?php esc_html_e( 'Languages menu item type', 'wp-multilang' ); ?></label>
			<select class="widefat" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $name ); ?>">
				<?php foreach ( $type_options as $val => $name ) { ?>
					<option value="<?php esc_attr_e( $val ); ?>"<?php selected( $val, $value ) ?>><?php esc_html_e( $name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
		$_key  = 'languages_show';
		$key   = sprintf( 'menu-item-%s', $_key );
		$id    = sprintf( 'edit-%s-%s', $key, $item_id );
		$name  = sprintf( '%s[%s]', $_key, $item_id );
		$value = get_post_meta( $item_id, '_menu_item_languages_show', true );
		$class = sprintf( 'field-%s', $_key );

		$show_options = array(
			'both' => __( 'Both', 'wp-multilang' ),
			'flag' => __( 'Flag', 'wp-multilang' ),
			'name' => __( 'Name', 'wp-multilang' ),
		);
		?>
		<p class="description description-wide <?php echo esc_attr( $class ); ?>">
			<label for="<?php esc_attr_e( $id ); ?>"><?php esc_html_e( 'Show', 'wp-multilang' ); ?></label>
			<select class="widefat" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $name ); ?>">
				<?php foreach ( $show_options as $val => $name ) { ?>
					<option value="<?php esc_attr_e( $val ); ?>"<?php selected( $val, $value ) ?>><?php esc_html_e( $name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}
}
