<?php
/**
 * Class for capability with Max Mega Menu
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Megamenu
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Megamenu {

	/**
	 * WPM_Megamenu constructor.
	 */
	public function __construct() {
		add_action( 'admin_print_scripts-mega-menu_page_maxmegamenu_theme_editor', array( $this, 'add_language_switcher' ), 11 );
		add_filter( 'widget_update_callback', array( $this, 'pre_save_widget' ), 100, 3 );
	}


	/**
	 * Add language switcher
	 */
	public function add_language_switcher() {

		if ( count( wpm_get_languages() ) <= 1 ) {
			return;
		}

		add_action( 'admin_print_footer_scripts', 'wpm_admin_language_switcher' );

		wpm_enqueue_js( "
		    if ($('#wpm-language-switcher').length === 0) {
				var language_switcher = wp.template( 'wpm-ls' );
				$('#wpbody-content .megamenu_outer_wrap').first().prepend(language_switcher);
		    }
		" );
	}

	/**
	 * Fix save widgets in menu item
	 *
	 * @param $instance
	 * @param $new_instance
	 * @param $old_instance
	 *
	 * @return array
	 */
	public function pre_save_widget( $instance, $new_instance, $old_instance ) {

		if ( isset( $old_instance['mega_menu_columns'] ) && ! isset( $new_instance['mega_menu_columns'] ) ) {
			$instance['mega_menu_columns'] = $old_instance['mega_menu_columns'];
		}

		if ( isset( $old_instance['mega_menu_order'] ) && ! isset( $new_instance['mega_menu_order'] ) ) {
			$instance['mega_menu_order'] = $old_instance['mega_menu_order'];
		}

		if ( isset( $old_instance['mega_menu_parent_menu_id'] ) && ! isset( $new_instance['mega_menu_parent_menu_id'] ) ) {
			$instance['mega_menu_parent_menu_id'] = $old_instance['mega_menu_parent_menu_id'];
		}

		return $instance;
	}
}
