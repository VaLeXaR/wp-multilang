<?php
/**
 * Setup menus in WP admin.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPM/Core/Admin
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Menus {

	/**
	 * WPM_Admin_Menus constructor.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );

		// Add endpoints custom URLs in Appearance > Menus > Pages.
		add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );

		if ( ! has_action( 'wp_nav_menu_item_custom_fields' ) ) {
			add_filter( 'wp_edit_nav_menu_walker', array( $this, 'filter_walker' ) );
		}

		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_languages_setting' ), 5, 2 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'update_nav_menu_item' ), 10, 2 );
	}


	/**
	 * Replace default menu editor walker with ours
	 *
	 * We don't actually replace the default walker. We're still using it and
	 * only injecting some HTMLs.
	 *
	 * @wp_hook filter wp_edit_nav_menu_walker
	 * @return  string Walker class name
	 */
	public static function filter_walker() {
		return 'WPM\Core\Libraries\WPM_Walker_Nav_Menu_Edit';
	}

	/**
	 * Add language switcher to admin
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {

		if ( ! get_current_user_id() ) {
			return;
		}

		$locale = get_locale();
		$languages = wpm_get_languages();

		if ( count( $languages ) <= 1 ) {
			return;
		}

		$options = wpm_get_options();

		$wp_admin_bar->add_menu( array(
			'id'     => 'wpm-language-switcher',
			'parent' => 'top-secondary',
			'title'  => '<span class="ab-icon">' .
			            '<img src="' . esc_url( WPM()->flag_dir() . $options[ $locale ]['flag'] . '.png' ) . '"/>' .
			            '</span><span class="ab-label">' .
			            $options[ $locale ]['name'] .
			            '</span>',
		) );

		$current_url = wpm_get_current_url();

		foreach ( $languages as $key => $language ) {

			if ( $key === $locale ) {
				continue;
			}

			$wp_admin_bar->add_menu( array(
				'parent' => 'wpm-language-switcher',
				'id'     => 'wpm-language-' . $language,
				'title'  => '<span class="ab-icon">' .
				            '<img src="' . esc_url( WPM()->flag_dir() . $options[ $key ]['flag'] . '.png' ) . '" />' .
							'</span>' .
				            '<span class="ab-label">' . $options[ $key ]['name'] . '</span>',
				'href'   => add_query_arg( 'lang', $language, $current_url ),
			) );
		}
	}

	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 */
	public function add_nav_menu_meta_boxes() {
		add_meta_box( 'wpm_endpoints_nav_link', __( 'Languages', 'wpm' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Output menu link.
	 */
	public function nav_menu_links() {
		global $_nav_menu_placeholder;
		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		?>
		<div id="posttype-wpm-languages" class="posttypediv">
			<div id="tabs-panel-wpm-languages" class="tabs-panel tabs-panel-active">
				<ul id="wpm-languages-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-object-id]" value="<?php esc_attr_e( $_nav_menu_placeholder ); ?>" /> <?php esc_html_e( 'Languages', 'wpm' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-type]" value="custom" />
						<input type="hidden" class="menu-item-title" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-title]" value="<?php esc_html_e( 'Languages', 'wpm' ); ?>" />
						<input type="hidden" class="menu-item-url" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-url]" value="#wpm-languages" />
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-classes]" value="wpm-languages" />
					</li>
				</ul>
			</div>
			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu' ); ?>" name="add-post-type-menu-item" id="submit-posttype-wpm-languages">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}


	/**
	 * Add custom fields to menu item.
	 *
	 * @param int $item_id
	 * @param object $item
	 */
	public function menu_item_languages_setting( $item_id, $item ) {

		if ( '#wpm-languages' != $item->url ) {
			return;
		}

		$_key  = 'language_show';
		$key   = sprintf( 'menu-item-%s', $_key );
		$id    = sprintf( 'edit-%s-%s', $key, $item_id );
		$name  = sprintf( '%s[%s]', $_key, $item_id );
		$value = get_post_meta( $item_id, '_menu_item_languages_show', true );
		$class = sprintf( 'field-%s', $_key );
		$options = array(
			'both' => __( 'Both', 'wpm' ),
			'flag' => __( 'Flag', 'wpm' ),
			'name' => __( 'Name', 'wpm' ),
		)
		?>
		<p class="description description-wide <?php echo esc_attr( $class ); ?>">
			<label for="<?php esc_attr_e( $id ); ?>"><?php esc_html_e( 'Show', 'wpm' ); ?></label>
			<select class="widefat" id="<?php esc_attr_e( $id ); ?>" name="<?php esc_attr_e( $name ); ?>">
				<?php foreach ( $options as $val => $name ) { ?>
					<option value="<?php esc_attr_e( $val ); ?>"<?php selected( $val, $value ) ?>><?php esc_html_e( $name ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}


	/**
	 * Add language settings params
	 *
	 * @param $menu_id
	 * @param $menu_item_db_id
	 */
	public function update_nav_menu_item( $menu_id, $menu_item_db_id ) {

		if( 'update' !== $_REQUEST['action'] ) {
			return;
		}

		check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

		if ( empty( $_POST['menu-item-url'][ $menu_item_db_id ] ) || '#wpm-languages' != $_POST['menu-item-url'][ $menu_item_db_id ] ) {
			return;
		}

		update_post_meta( $menu_item_db_id, '_menu_item_languages_show', $_POST['language_show'][ $menu_item_db_id ] );
	}
}
