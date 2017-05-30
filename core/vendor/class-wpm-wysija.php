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

		public $translate_config = array(
			'company_address'               => array(),
			'commentform_linkname'          => array(),
			'viewinbrowser_linkname'        => array(),
			'unsubscribe_linkname'          => array(),
			'confirm_email_title'           => array(),
			'confirm_email_body'            => array(),
			'manage_subscriptions_linkname' => array()
		);

		/**
		 * WPM_Wysija constructor.
		 */
		public function __construct() {
			add_filter( 'hook_settings_before_save', array( $this, 'save_settings' ), 10, 2 );
			$this->translate_settings();
		}


		public function save_settings( $hook_settings_before_save1, $hook_settings_before_save ) {

			$old_config = wpm_value_to_ml_array( maybe_unserialize( base64_decode( get_option( 'wysija' ) ) ) );

			foreach ( $this->translate_config as $key => $item_translate_config ) {
				$new_value                            = wpm_set_language_value( $old_config[ $key ], $hook_settings_before_save['REQUEST']['wysija']['config'][ $key ], $item_translate_config );
				$_REQUEST['wysija']['config'][ $key ] = wpm_ml_value_to_string( $new_value );
			}

			return $hook_settings_before_save1;
		}


		public function translate_settings() {
			global $modelConf;
			foreach ( $this->translate_config as $key => $item_translate_config ) {
				if ( isset( $modelConf->values[ $key ] ) ) {
					$modelConf->values[ $key ] = wpm_translate_value( $modelConf->values[ $key ] );
				}
			}
		}
	}

	new WPM_Wysija();
}
