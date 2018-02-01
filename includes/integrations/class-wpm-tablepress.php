<?php
/**
 * Class for capability with TablePress
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Tablepress
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
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

	/**
	 * Translate table
	 *
	 * @param $data
	 * @param $action
	 *
	 * @return mixed
	 */
	public function translate_edit_table( $data, $action ) {
		if ( 'edit' === $action ) {
			$data['table'] = wpm_translate_value( $data['table'] );
		}

		return $data;
	}


	/**
	 * Save table
	 *
	 * @param $data
	 * @param $postarr
	 *
	 * @return mixed
	 */
	public function save_table( $data, $postarr ) {

		if ( ! $postarr['ID'] || ( 'tablepress_table' !== $postarr['post_type'] ) ) {
			return $data;
		}

		$options = array(
			'wpm_each' => array(
				'wpm_each' => array(),
			),
		);

		$old_table            = json_decode( get_post_field( 'post_content', wpm_clean( $postarr['ID'] ), 'edit' ), true );
		$new_table            = json_decode( stripslashes_from_strings_only( $data['post_content'] ), true );
		$value                = wpm_set_new_value( $old_table, $new_table, $options );
		$data['post_content'] = addslashes_gpc( wp_json_encode( $value ) );

		return $data;
	}
}
