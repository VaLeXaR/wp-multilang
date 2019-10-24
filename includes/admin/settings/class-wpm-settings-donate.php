<?php
/**
 * WP Multilang Additional Settings
 *
 * @category    Admin
 * @package     WPM/Admin
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Settings_General.
 */
class WPM_Settings_Donate extends WPM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'donate';
		$this->label = __( 'Donate', 'wp-multilang' );

		parent::__construct();

		add_action( 'wpm_admin_field_donate', array( $this, 'get_donate' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		// Hide the save button
		$GLOBALS['hide_save_button'] = true;

		$settings = apply_filters( 'wpm_' . $this->id . '_settings', array(

			array( 'title' => __( 'Donate', 'wp-multilang' ), 'type' => 'title', 'desc' => '', 'id' => 'donate_options' ),

			array(
				'title' => __( 'Donate', 'wp-multilang' ),
				'id'    => 'wpm_donate',
				'type'  => 'donate',
			),

			array( 'type' => 'sectionend', 'id' => 'donate_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}

	public function get_donate( $value ) {
		include_once __DIR__ . '/views/html-donate.php';
	}
}
