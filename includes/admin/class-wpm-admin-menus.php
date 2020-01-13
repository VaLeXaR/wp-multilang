<?php
/**
 * Setup menus in WP admin.
 *
 * @author   Valentyn Riaboshtan
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPM_Admin_Menus {

	/**
	 * WPM_Admin_Menus constructor.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );

		// Add endpoints custom URLs in Appearance > Menus > Pages.
		add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );
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

		$installed_translations = wpm_get_installed_languages();

		if ( count( $installed_translations ) <= 1 ) {
			return;
		}

		$user_language          = wpm_get_user_language();
		$languages              = wpm_get_languages();
		$available_translations = wpm_get_available_translations();

		if ( isset( $languages[ $user_language ] ) ) {
			$wp_admin_bar->add_menu( array(
				'id'     => 'wpm-language-switcher',
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon">' .
				            ( $languages ? '<img src="' . esc_url( wpm_get_flag_url( $languages[ $user_language ]['flag'] ) ) . '"/>' : '' ) . '</span><span class="ab-label">' . $available_translations[ get_locale() ]['native_name'] . '</span>',
			) );
		}

		foreach ( $installed_translations as $locale ) {

			if ( get_locale() === $locale ) {
				continue;
			}

			$code     = '';
			$language = array();
			$add = false;

			foreach ( $languages as $code => $language ) {
				if ( isset( $language['translation'] ) && ( $language['translation'] == $locale ) ) {
					$add = true;
					break;
				}
			}

			if ( $add ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'wpm-language-switcher',
					'id'     => 'wpm-language-' . $code,
					'title'  => '<span class="ab-icon">' . '<img src="' . esc_url( wpm_get_flag_url( $language['flag'] ) ) . '" />' . '</span>' . '<span class="ab-label">' . $available_translations[$locale]['native_name'] . '</span>',
					'href'   => wpm_translate_current_url( $code ),
				) );
			}
		}
	}


	/**
	 * Add menu item.
	 */
	public function settings_menu() {
		$settings_page = add_options_page( __( 'WP Multilang Settings', 'wp-multilang' ), __( 'WP Multilang', 'wp-multilang' ), 'manage_options', 'wpm-settings', array( $this, 'settings_page' ) );

		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}

	/**
	 * Load settings.
	 */
	public function settings_page_init() {
		global $current_tab, $current_section;

		// Include settings pages
		WPM_Admin_Settings::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			WPM_Admin_Settings::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['wpm_error'] ) ) {
			WPM_Admin_Settings::add_error( stripslashes( $_GET['wpm_error'] ) );
		}

		if ( ! empty( $_GET['wpm_message'] ) ) {
			WPM_Admin_Settings::add_message( stripslashes( $_GET['wpm_message'] ) );
		}
	}

	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		WPM_Admin_Settings::output();
	}


	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 */
	public function add_nav_menu_meta_boxes() {
		add_meta_box( 'wpm_endpoints_nav_link', __( 'Languages', 'wp-multilang' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
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
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-object-id]" value="<?php esc_attr_e( $_nav_menu_placeholder ); ?>" /> <?php esc_html_e( 'Languages', 'wp-multilang' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-type]" value="custom" />
						<input type="hidden" class="menu-item-title" name="menu-item[<?php esc_attr_e( $_nav_menu_placeholder ); ?>][menu-item-title]" value="<?php esc_html_e( 'Languages', 'wp-multilang' ); ?>" />
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
}
