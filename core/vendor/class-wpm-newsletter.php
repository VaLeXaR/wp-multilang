<?php
/**
 * Class for capability with Newsletter
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'NEWSLETTER_VERSION' ) ) {
	return;
}

/**
 * Class WPM_Newsletter
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.2.1
 */
class WPM_Newsletter {

	/**
	 * WPM_Newsletter constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_newsletter_profile_config', array( $this, 'add_options_config' ) );
		add_filter( 'newsletter_message_subject', 'wpm_translate_string' );
		add_filter( 'newsletter_message_html', 'wpm_translate_string' );
		add_action( 'admin_notices', array( $this, 'add_notice' ) );
		add_action( 'init', array( $this, 'translate_options' ) );
	}


	public function add_options_config( $config ) {
		for ( $i = 1; $i <= 20; $i ++ ) {
			$config[ "profile_{$i}" ]             = array();
			$config[ "profile_{$i}_placeholder" ] = array();
		}

		return $config;
	}


	/**
	 * Translate some texts without PHP filters by javascript for displaying
	 */
	public function add_notice() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'admin_page_newsletter_emails_edit' === $screen_id ) {
			remove_filter( 'attribute_escape', array( 'WPM\Core\WPM_Posts', 'escaping_text' ), 0 );
			remove_filter( 'esc_textarea', array( 'WPM\Core\WPM_Posts', 'escaping_text' ), 0 );
			remove_filter( 'esc_html', array( 'WPM\Core\WPM_Posts', 'escaping_text' ), 0 );
			wpm_show_notice();
		}
	}


	/**
	 * Translate options
	 */
	public function translate_options(){
		\NewsletterSubscription::instance()->options = wpm_translate_value( \NewsletterSubscription::instance()->options );
		\Newsletter::instance()->options = wpm_translate_value( \Newsletter::instance()->options );

		/**
		 * Compatibility with extension WP Users Integration
		 */
		if ( class_exists( 'NewsletterWpUsers' ) ) {
			\NewsletterWpUsers::$instance->options = wpm_translate_value( \NewsletterWpUsers::$instance->options );
		}

		/**
		 * Compatibility with extension Locked Content
		 */
		if (class_exists( 'NewsletterLock')) {
			\NewsletterLock::$instance->options = wpm_translate_value( \NewsletterLock::$instance->options );
		}
	}
}

new WPM_Newsletter();
