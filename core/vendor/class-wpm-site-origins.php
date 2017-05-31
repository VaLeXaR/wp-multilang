<?php
/**
 * Class for capability with Visual Composer
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'SITEORIGIN_PANELS_VERSION' ) ) {

	/**
	 * Class WPM_Site_Origins
	 * @since 1.2.0
	 */
	class WPM_Site_Origins {

		/**
		 * WPM_Site_Origins constructor.
		 */
		public function __construct() {
			add_filter( 'wpm_filter_old_meta_value', function ( $old_value, $meta_value ) {

				return $old_value;
			}, 10, 2 );

//			"frames": {
//				"wpm_each": {
//					"content": {}
//            }
		}
	}

	new WPM_Site_Origins();
}
