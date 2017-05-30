<?php
/**
 * Class for capability with Visual Composer
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WYSIJA' ) ) {

	/**
	 * Class WPM_Wysija
	 * @since 1.1.2
	 */
	class WPM_Wysija {

		/**
		 * WPM_Wysija constructor.
		 */
		public function __construct() {
			add_filter( 'hook_settings_before_save', function($hook_settings_before_save1, $hook_settings_before_save){

				var_dump( maybe_unserialize( base64_decode( get_option( 'wysija') ) ) );

				$translate_config = array(
					'company_address'               => array(),
					'commentform_linkname'          => array(),
					'viewinbrowser_linkname'        => array(),
					'unsubscribe_linkname'          => array(),
					'confirm_email_title'           => array(),
					'confirm_email_body'            => array(),
					'manage_subscriptions_linkname' => array()
				);

				$old_config = wpm_value_to_ml_array( \WYSIJA::get('config', 'model') );
//				$new_config = wpm_set_language_value( $old_config, $hook_settings_before_save, $translate_config );
//				$field     = wpm_array_merge_recursive( $field, $new_config );
//				$_REQUEST['wysija']['config'] = wpm_ml_value_to_string( $field );
				var_dump( $old_config, $hook_settings_before_save1, $hook_settings_before_save);
				die();
				return $hook_settings_before_save1;
			}, 10, 2);
		}
	}

	new WPM_Wysija();
}
