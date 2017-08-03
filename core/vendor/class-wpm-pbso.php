<?php
/**
 * Class for capability with Page Builder by SiteOrigin
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SITEORIGIN_PANELS_VERSION' ) ) {
	return;
}

/**
 * Class WPM_PBSO
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.2.0
 */
class WPM_PBSO {

	/**
	 * WPM_PBSO constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_filter_old_panels_data_meta_value', array( $this, 'filter_old_value' ), 10, 2 );
		add_filter( 'wpm_panels_data_meta_config', array( $this, 'add_recursive_config' ), 10, 2 );
		add_filter( 'siteorigin_widgets_search_posts_results', 'wpm_translate_value' );
	}

	/**
	 * Filter old values recursively
	 *
	 * @param $old_value
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function filter_old_value( $old_value, $meta_value ) {

		$new_old_value            = $meta_value;
		$new_old_value['widgets'] = array();

		foreach ( $meta_value['widgets'] as $key => $widget ) {

			foreach ( $old_value['widgets'] as $_widget ) {

				if ( $widget['_sow_form_id'] === $_widget['_sow_form_id'] ) {
					$new_old_value['widgets'][ $key ] = $_widget;

					if ( isset( $widget['frames'] ) ) {

						foreach ( $widget['frames'] as $_key => $frame ) {

							if ( isset( $frame['content'] ) && is_array( $frame['content'] ) && isset( $frame['content']['widgets'] ) ) {

								foreach ( $_widget['frames'] as $_frame ) {
									$new_old_value['widgets'][ $key ]['frames'][ $_key ]['content'] = $this->filter_old_value( $_frame['content'], $frame['content'] );
								}
							}
						}
					}
				}
			}
		}

		return $new_old_value;
	}


	/**
	 * Add config for values recursively
	 *
	 * @param $config
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function add_recursive_config( $config, $meta_value ) {

		if ( is_array( $meta_value ) && isset( $meta_value['widgets'] ) ) {

			foreach ( $meta_value['widgets'] as $widget ) {

				if ( isset( $widget['frames'] ) ) {

					foreach ( $widget['frames'] as $frame ) {

						if ( isset( $frame['content'] ) && is_array( $frame['content'] ) && isset( $frame['content']['widgets'] ) ) {
							$config['widgets']['wpm_each']['frames']['wpm_each']['content'] = $this->add_recursive_config( $config, $frame['content'] );
						}
					}
				}
			}
		}

		return $config;
	}
}

new WPM_PBSO();
