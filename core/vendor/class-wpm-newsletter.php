<?php
/**
 * Class for capability with Newsletter
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'NEWSLETTER_VERSION' ) ) {

	/**
	 * Class WPM_Newsletter
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @since    1.2.0
	 */
	class WPM_Newsletter {

		/**
		 * WPM_Newsletter constructor.
		 */
		public function __construct() {
			$newsletterwp          = \NewsletterWp::instance();
			$newsletterwp->options = wpm_translate_value( $newsletterwp->options );
			add_filter( 'wpm_option_newsletter_profile_config', array( $this, 'add_options_config' ) );
			add_filter( 'newsletter_message_subject', 'wpm_translate_string' );
			add_filter( 'newsletter_message_html', 'wpm_translate_string' );
		}


		public function add_options_config( $config ) {
			for ( $i = 1; $i <= 20; $i ++ ) {
				$config["profile_{$i}"]             = array();
				$config["profile_{$i}_placeholder"] = array();
			}

			return $config;
		}
	}

	new WPM_Newsletter();
}
