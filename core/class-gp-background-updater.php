<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    GP_Background_Updater
 * @package  GamePortal/Classes
 * @category Class
 * @author   VaLeXaR
 */

namespace GP;

use GP\Libraries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( 'libraries/wp-async-request.php' );
include_once( 'libraries/wp-background-process.php' );

/**
 * WC_Background_Updater Class.
 */
class GP_Background_Updater extends Libraries\WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'gp_updater';

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'GP_UPDATING' ) ) {
			define( 'GP_UPDATING', true );
		}

		include_once( 'gp-update-functions.php' );

		if ( is_callable( $callback ) ) {
			call_user_func( $callback );
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		GP_Install::update_db_version();
		parent::complete();
	}
}
