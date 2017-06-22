<?php
/**
 * Translate widgets in admin
 *
 * @package  WPM\Core\Admin
 * @class    WPM_Admin_Widgets
 * @category Admin
 * @author   VaLeXaR
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WPM_Admin_Widgets
 */
class WPM_Admin_Widgets {

	/**
	 * WPM_Admin_Widgets constructor.
	 */
	public function __construct() {
		add_filter( 'widget_form_callback', 'wpm_translate_value', 0 );
		add_filter( 'widget_update_callback', array( $this, 'pre_save_widget' ), 99, 4 );
	}

	/**
	 * Update widget translation. Title and text field translate for all widgets.
	 *
	 * @param $instance
	 * @param $new_instance
	 * @param $old_instance
	 * @param $widget
	 *
	 * @return array
	 *
	 */
	public function pre_save_widget( $instance, $new_instance, $old_instance, $widget ) {

		$config        = wpm_get_config();
		$widget_config = array(
			'title' => array(),
			'text'  => array(),
		);

		if ( isset( $config['widgets'][ $widget->id_base ] ) ) {
			$widget_config = wpm_array_merge_recursive( $widget_config, $config['widgets'][ $widget->id_base ] );
		}

		$widget_config = apply_filters( "wpm_widget_{$widget->id_base}_config", $widget_config, $instance );

		if ( wpm_is_ml_value( $old_instance ) ) {
			$old_instance = wpm_value_to_ml_array( $old_instance );
		}

		$new_value = wpm_set_language_value( $old_instance, $new_instance, $widget_config );
		$instance  = wpm_ml_value_to_string( $new_value );

		return $instance;
	}
}
