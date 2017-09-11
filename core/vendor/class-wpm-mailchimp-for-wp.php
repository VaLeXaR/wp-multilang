<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MC4WP_VERSION' ) ) {
	return;
}

/**
 * Class WPM_MailChimp_For_WP
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.2.0
 * @version  1.1
 */
class WPM_MailChimp_For_WP {

	/**
	 * WPM_MailChimp_For_WP constructor.
	 */
	public function __construct() {
		add_filter( 'mc4wp_form_content', 'wpm_translate_string' );
		add_filter( 'mc4wp_integration_checkbox_label', 'wpm_translate_string' );
		add_action('mc4wp_admin_edit_form_output_fields_tab', 'wpm_show_notice');
	}
}

new WPM_MailChimp_For_WP();
