<?php
/**
 * GamePortal Admin Functions
 *
 * @author   VaLeXaR
 * @category Core
 * @package  GamePortal/Admin/Functions
 */

use GP\Admin\GP_Admin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all Game Portal screen ids.
 *
 * @return array
 */
function gp_get_screen_ids() {

	$gp_screen_id = sanitize_title( __( 'Game Portal', 'game-portal' ) );
	$screen_ids   = array(
		$gp_screen_id . '_page_gp-settings',
		'level',
		'edit-level',
		'edit-game',
		'profile',
		'user-edit',
		'upload'
	);

	return $screen_ids;
}

/**
 * Output admin fields.
 *
 * Loops though the game-portal options array and outputs each field.
 *
 * @param array $options Opens array to output
 */
function game_portal_admin_fields( $options ) {

	GP_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @param array $options
 */
function game_portal_update_options( $options ) {

	GP_Admin_Settings::save_fields( $options );
}

/**
 * Get a setting from the settings API.
 *
 * @param mixed $option_name
 * @param mixed $default
 * @return string
 */
function game_portal_settings_get_option( $option_name, $default = '' ) {

	return GP_Admin_Settings::get_option( $option_name, $default );
}
