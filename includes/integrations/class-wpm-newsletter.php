<?php
/**
 * Class for capability with Newsletter
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Newsletter
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Newsletter {

	/**
	 * WPM_Newsletter constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_option_newsletter_profile_config', array( $this, 'add_options_config' ) );
		add_action( 'admin_notices', array( $this, 'add_notice' ) );
		add_action( 'init', array( $this, 'translate_options' ) );
		add_filter( 'newsletter_user_subscribe', array( $this, 'save_profile_20' ) );
		add_filter( 'newsletter_replace', array( $this, 'translate_email' ), 10, 2 );
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
			remove_filter( 'attribute_escape', 'wpm_escaping_text', 5 );
			remove_filter( 'esc_textarea', 'wpm_escaping_text', 5 );
			remove_filter( 'esc_html', 'wpm_escaping_text', 5 );
			wpm_show_notice();
		}
	}


	/**
	 * Translate options
	 */
	public function translate_options() {
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
		if ( class_exists( 'NewsletterLock' ) ) {
			\NewsletterLock::$instance->options = wpm_translate_value( \NewsletterLock::$instance->options );
		}
	}


	public function save_profile_20( $data ) {
		$data['profile_20'] = wpm_get_language();

		return $data;
	}


	public function translate_email( $text, $user ) {

		if ( is_object( $user ) && $user->profile_20 ) {
			$text = wpm_translate_string( $text, $user->profile_20 );
		}

		return $text;
	}
}
