<?php
/**
 * WP Multilang Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @category Core
 * @package  WPM/Functions
 */

use WPM\Core\WPM_Install;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update date and time format foe languages.
 */
function wpm_update_178_datetime_format() {

}

/**
 * Update DB Version.
 */
function wpm_update_178_db_version() {
	WPM_Install::update_db_version( '1.7.8' );
}
