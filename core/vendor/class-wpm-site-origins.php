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
			add_filter( 'wpm_post_meta_config', function ( $config ) {
				$config['panels_data'] = array(
					'widgets' => array(
						'wpm_each' => array(
							'title' => array(),
							'text' => array(),
							'markers' => array(
								'marker_positions' => array(
									'wpm_each' => array(
										'info' => array()
									)
								)
							),
							'attributes' => array(
								'title' => array()
							),
							'settings' => array(
								'default_subject' => array(),
								'subject_prefix' => array(),
								'success_message' => array(),
								'submit_text' => array(),
								'required_field_indicator_message' => array()
							),
							'fields' => array(
								'wpm_each' => array(
									'label' => array(),
									'description' => array(),
									'required' => array(
										'missing_message' => array()
									)
								)
							),
							'features' => array(
								'wpm_each' => array(
									'icon_title' => array(),
									'title' => array(),
									'text' => array(),
									'more_text' => array()
								)
							)
						)
					)
				);

				return $config;
			} );
			/*add_filter( 'siteorigin_panels_use_cached', function( $cache ){
				var_dump( $cache);
				die();
				return $cache;
			} );*/
		}
	}

	new WPM_Site_Origins();
}
