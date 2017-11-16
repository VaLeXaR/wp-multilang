<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_MailChimp_For_WP
 * @package  WPM/Includes/Vendor
 * @category Vendor
 * @author   Valentyn Riaboshtan
 */
class WPM_MailChimp_For_WP {

	/**
	 * WPM_MailChimp_For_WP constructor.
	 */
	public function __construct() {
		add_filter( 'mc4wp_form_content', 'wpm_translate_string' );
		add_filter( 'mc4wp_integration_checkbox_label', 'wpm_translate_string' );
		add_action( 'mc4wp_admin_edit_form_output_fields_tab', 'wpm_show_notice' );
	}
}
