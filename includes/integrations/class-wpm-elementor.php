<?php
/**
 * Class for capability with Elementor Page Builder
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_Elementor
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_Elementor {

	/**
	 * WPM_Elementor constructor.
	 */
	public function __construct() {
		/*add_filter( 'elementor/frontend/builder_content_data', function ( $data ) {
			s( $data );
			die();

			return $data;
		} );*/

		/*add_filter('wpm_get__elementor_data_meta_value', function($value){
			d($value);
			die();
			return $value;
		});*/
	}
}
