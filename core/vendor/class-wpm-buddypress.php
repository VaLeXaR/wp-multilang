<?php
/**
 * Class for capability with BuddyPress
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BuddyPress' ) ) {
	return;
}

/**
 * Class WPM_BuddyPress
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 */
class WPM_BuddyPress {

	/**
	 * WPM_BuddyPress constructor.
	 */
	public function __construct() {
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'change_url_customizer_previewer' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_post_custom_fields' ), 40 );
	}

	public function change_url_customizer_previewer() {

		if ( bp_is_email_customizer() ) {
			add_filter( 'wpm_customizer_url', function () {
				return rawurldecode( wpm_clean( $_GET['url'] ) );
			} );
		}
	}


	public function remove_post_custom_fields() {
		remove_meta_box( 'wpm-bp-email-languages', 'bp-email', 'side' );
	}
}

new WPM_BuddyPress();
