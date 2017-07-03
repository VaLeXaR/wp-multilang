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
	}

	/**
	 * Add language switcher to admin
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 *
	 * @return bool
	 */
	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$locale = get_locale();
		$languages = wpm_get_languages();

		if ( count( $languages ) <= 1 ) {
			return false;
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
}
