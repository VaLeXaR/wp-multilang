<?php
/**
 * Translate widgets in admin
 *
 * @package  WPM/Includes/Admin
 * @class    WPM_Admin_Widgets
 * @category Admin
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes\Admin;

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
		add_filter( 'widget_form_callback', 'wpm_translate_value', 5 );
		add_filter( 'widget_update_callback', array( $this, 'pre_save_widget' ), 99, 4 );
		add_action( 'in_widget_form', array( $this, 'add_language_fields' ), 15, 3 );
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

		$widget_config = wpm_get_widget_config( $widget->id_base );

		if ( null === $widget_config ) {
			return $instance;
		}

		$instance = wpm_set_new_value( $old_instance, $new_instance, $widget_config );

		return $instance;
	}


	/**
	 * Add language select field
	 *
	 * @param $widget
	 * @param $return
	 * @param object \WP_Widget $instance
	 */
	public function add_language_fields( $widget, $return, $instance ) {

		if ( null === wpm_get_widget_config( $widget->id_base ) ) {
			return;
		}

		$instance  = wp_parse_args( (array) $instance, array( 'languages' => array() ) );
		$languages = wpm_get_languages();
		$i         = 0;
		?>
		<p>
			<?php _e( 'Show widget only in:', 'wp-multilang' ); ?><br>
			<?php foreach ( $languages as $code => $language ) { ?>
				<label><input type="checkbox" name="<?php echo esc_attr_e( $widget->get_field_name('languages') ); ?>[<?php echo esc_attr( $i ) ; ?>]" id="<?php echo esc_attr( $widget->get_field_id('languages') . '-' . $code ); ?>" value="<?php echo esc_attr( $code ); ?>"<?php checked( in_array( $code, $instance['languages'] ) ); ?>><?php esc_html_e( $language['name'] ); ?></label><br>
			<?php $i++; } ?>
		</p>
		<?php
	}
}
