<?php
/**
 * Class for capability with Page Builder by SiteOrigin
 */

namespace WPM\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPM_PBSO
 * @package  WPM/Includes/Integrations
 * @category Integrations
 * @author   Valentyn Riaboshtan
 */
class WPM_PBSO {

	/**
	 * WPM_PBSO constructor.
	 */
	public function __construct() {
		add_filter( 'wpm_panels_data_meta_config', array( $this, 'add_recursive_config' ), 10, 2 );
		add_filter( 'wpm_update_panels_data_meta_value', array( $this, 'formatting_updated_value' ) );
		add_filter( 'wpm_update_panels_data_meta_value', 'wpm_translate_value' );
		add_filter( 'wpm_filter_old_panels_data_meta_value', array( $this, 'filter_old_value' ), 10, 2 );
		add_filter( 'siteorigin_widgets_search_posts_results', 'wpm_translate_value' );
	}

	/**
	 * Decode JSON string in new value
	 *
	 * @param array $meta_value
	 *
	 * @return array
	 */
	public function formatting_updated_value( $meta_value ) {

		if ( is_array( $meta_value ) && isset( $meta_value['widgets'] ) ) {
			foreach ( $meta_value['widgets'] as $key => $widget ) {
				if ( isset( $widget['frames'] ) ) {
					foreach ( $widget['frames'] as $_key => $frame ) {
						if ( ! empty( $frame['content'] ) ) {

							if ( isJSON( $frame['content'] ) ) {
								$frame['content'] = json_decode( $frame['content'], true );
							}

							if ( ! empty( $frame['content']['widgets'] )) {
								$meta_value['widgets'][ $key ]['frames'][ $_key ]['content'] = $this->formatting_updated_value( $frame['content'] );
							}
						}
					}
				}
			}
		}

		return $meta_value;
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
				if ( $widget['panels_info']['widget_id'] === $_widget['panels_info']['widget_id'] ) {
					$new_old_value['widgets'][ $key ] = $_widget;
					if ( ! empty( $widget['frames'] ) ) {
						foreach ( $widget['frames'] as $_key => $frame ) {
							if ( ! empty( $frame['content'] ) && isset( $frame['content']['widgets'] ) ) {
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
	 * @param array $meta_value
	 *
	 * @return array
	 */
	public function add_recursive_config( $config, $meta_value ) {

		if ( is_array( $meta_value ) && isset( $meta_value['widgets'] ) ) {
			foreach ( $meta_value['widgets'] as $widget ) {
				if ( isset( $widget['frames'] ) ) {
					foreach ( $widget['frames'] as $frame ) {
						if ( ! empty( $frame['content'] ) ) {
							if ( isJSON( $frame['content'] ) ) {
								$frame['content'] = json_decode( $frame['content'], true );
							}

							if ( ! empty( $frame['content']['widgets'] ) ) {
								$config['widgets']['wpm_each']['frames']['wpm_each']['content'] = $this->add_recursive_config( $config, $frame['content'] );
							}
						}
					}
				}
			}
		}

		return $config;
	}
}
