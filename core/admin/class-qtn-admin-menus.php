<?php
/**
 * Setup menus in WP admin.
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  qTranslateNext/Admin
 * @version  1.0.0
 */

namespace QtNext\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QtN_Admin_Menus' ) ) :

/**
 * QtN_Admin_Menus Class.
 */
class QtN_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ) );
	}

	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {
		global $qtn_config;

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$locale = get_locale();
		$languages = $qtn_config->languages;

		if ( count( $languages ) <= 1 ) {
			return false;
		}

		$options = $qtn_config->options;

		$wp_admin_bar->add_menu( array(
			'id'     => 'qtn-language-switcher',
			'parent' => 'top-secondary',
			'title'  => '<span class="ab-icon">' .
			            '<img src="' . QN()->flag_dir() . $options[$locale]['flag'] . '.png' . '"/>' .
			            '</span><span class="ab-label">' .
			            $options[$locale]['name'] .
			            '</span>',
		) );

		$current_url = home_url( $_SERVER['REQUEST_URI'] );

		foreach ( $languages as $key => $language ) {

			if ( $key === $locale ) {
				continue;
			}

			$wp_admin_bar->add_menu( array(
				'parent' => 'qtn-language-switcher',
				'id'     => 'qtn-language-' . $language,
				'title'  => '<img src="' . QN()->flag_dir() . $options[$key]['flag'] . '.png' . '" />' .
				            '&nbsp;&nbsp;' .
				            $options[ $key ]['name'],
				'href'   => add_query_arg( 'lang', $language, $current_url ),
			) );
		}
	}
}

endif;
