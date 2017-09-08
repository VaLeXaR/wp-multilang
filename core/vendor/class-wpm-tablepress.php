<?php
/**
 * Class for capability with TablePress
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TablePress' ) ) {
	return;
}

/**
 * Class WPM_Tablepress
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.6.0
 */
class WPM_Tablepress {

	/**
	 * WPM_Tablepress constructor.
	 */
	public function __construct() {
		add_filter( 'tablepress_table_render_data', 'wpm_translate_value' );
		add_filter( 'tablepress_view_data', array( $this, 'translate_edit_table' ), 10, 2 );
		add_filter( 'wp_insert_post_data', array( $this, 'save_table' ), 10, 2 );
	}


	public function translate_edit_table( $data, $action ) {
		if ( 'edit' == $action ) {
			$data['table'] = wpm_translate_value( $data['table'] );
		}

		return $data;
	}

	public function save_table( $data, $postarr ) {

		if ( ! $postarr['ID'] || ( 'tablepress_table' != $postarr['post_type'] ) ) {
			return $data;
		}

		$options = array(
			'wpm_each' => array(
				"wpm_each" => array()
			)
		);

		$old_table            = json_decode( get_post_field( 'post_content', wpm_clean( $postarr['ID'] ), 'edit' ) );
		$strings              = wpm_value_to_ml_array( $old_table );
		$value                = json_decode( stripslashes_from_strings_only( $data['post_content'] ) );
		$new_value            = wpm_set_language_value( $strings, $value, $options );
		$new_value            = wpm_ml_value_to_string( $new_value );
		$data['post_content'] = addslashes_gpc( wp_json_encode( $new_value ) );

		return $data;
	}
}

new WPM_Tablepress();
