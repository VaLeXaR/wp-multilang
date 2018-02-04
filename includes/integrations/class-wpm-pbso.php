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
		add_filter( 'wpm_get_panels_data_meta_value', array( $this, 'translate_value' ) );
		add_filter( 'wpm_update_panels_data_meta_value', array( $this, 'transform_value' ) );
		add_filter( 'wpm_filter_old_panels_data_meta_value', array( $this, 'filter_old_value' ), 10, 2 );
		add_filter( 'wpm_filter_new_panels_data_meta_value', array( $this, 'filter_new_value' ) );
		add_filter( 'wpm_panels_data_meta_config', array( $this, 'add_recursive_config' ), 10, 2 );
		add_filter( 'siteorigin_widgets_search_posts_results', 'wpm_translate_value' );
	}

	/**
	 * Translate value
	 *
	 * @param $meta_value
	 *
	 * @return array
	 */
	public function translate_value( $meta_value ) {

		$meta_value = wpm_translate_value( $meta_value );

		foreach ( $meta_value['widgets'] as $key => $widget ) {
			if ( isset( $widget['frames'] ) ) {
				foreach ( $widget['frames'] as $_key => $frame ) {
					if ( isset( $frame['content'] ) && is_string( $frame['content'] ) && json_decode( $frame['content'] ) ) {
						$frame['content'] = json_decode( $frame['content'], true );
						if ( is_array( $frame['content'] ) && isset( $frame['content']['widgets'] ) ) {
							$meta_value['widgets'][ $key ]['frames'][ $_key ]['content'] = $this->translate_value( $frame['content'] );
						}
					}
				}
			}
		}

		return $meta_value;
	}

	/**
	 * Transform value for set translation
	 *
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function transform_value( $meta_value ) {

		foreach ( $meta_value['widgets'] as $key => $widget ) {
			if ( isset( $widget['frames'] ) ) {
				foreach ( $widget['frames'] as $_key => $frame ) {
					if ( isset( $frame['content'] ) && json_decode( $frame['content'] ) ) {
						$frame['content'] = json_decode( $frame['content'], true );
						if ( is_array( $frame['content'] ) && isset( $frame['content']['widgets'] ) ) {
							$meta_value['widgets'][ $key ]['frames'][ $_key ]['content'] = $this->transform_value( $frame['content'] );
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
					if ( isset( $widget['frames'] ) ) {
						foreach ( $widget['frames'] as $_key => $frame ) {
							if ( is_array( $frame['content'] ) && isset( $frame['content'], $frame['content']['widgets'] ) ) {
								foreach ( $_widget['frames'] as $_frame ) {
									$_frame['content'] = json_decode( $_frame['content'], true );
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
	 * Filter new values recursively
	 *
	 * @param $meta_value
	 *
	 * @return mixed
	 */
	public function filter_new_value( $meta_value ) {

		foreach ( $meta_value['widgets'] as $key => $widget ) {
			if ( isset( $widget['frames'] ) ) {
				foreach ( $widget['frames'] as $_key => $frame ) {
					if ( is_array( $frame['content'] ) ) {
						$meta_value['widgets'][ $key ]['frames'][ $_key ]['content'] = wp_json_encode( $this->filter_new_value( $frame['content'] ) );
					}
				}
			}
		}

		return $meta_value;
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
						if ( isset( $frame['content'] ) && is_string( $frame['content'] ) && json_decode( $frame['content'] ) ) {
							$frame['content'] = json_decode( $frame['content'], true );
							if ( isset( $frame['content']['widgets'] ) ) {
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
