<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPCF7_VERSION' ) ) {
	return;
}

/**
 * Class WPM_CF7
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 */
class WPM_CF7 {

	/**
	 * WPM_CF7 constructor.
	 */
	public function __construct() {
		add_filter( 'wpcf7_special_mail_tags', array( $this, 'add_language_tag' ), 10, 2 );
		add_filter( 'wpcf7_form_hidden_fields', array( $this, 'add_lang_field' ) );
	}

	public function add_language_tag( $output, $name ) {
		if ( '_language' == $name ) {
			$options = wpm_get_options();

			return $options[ get_locale() ]['name'];
		}

		return $output;
	}


	/**
	 * Add current user language hidden field
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function add_lang_field( $fields ) {
		$fields['lang'] = wpm_get_language();

		return $fields;
	}
}

new WPM_CF7();
