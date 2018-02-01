<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_CF7
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
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
		if ( '_language' === $name ) {
			$options = wpm_get_lang_option();

			return $options[ wpm_get_language() ]['name'];
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
