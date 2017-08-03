<?php
/**
 * Class for capability with MasterSlider
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'MSWP_AVERTA_VERSION' ) ) {
	return;
}

/**
 * Class WPM_Masterslider
 * @package  WPM\Core\Vendor
 * @category Vendor
 * @author   VaLeXaR
 * @since    1.3.0
 */
class WPM_Masterslider {

	/**
	 * WPM_Masterslider constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msp_panel_handler', array( $this, 'save_slider' ), 0 );
		add_action( 'masterslider_admin_add_panel_variables', array( $this, 'translate_slider' ) );
		add_filter( 'wpm_admin_pages', array( $this, 'add_language_switcher' ) );
	}


	/**
	 * Save slider
	 */
	public function save_slider() {
		// verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'msp_panel' ) ) {
			return;
		}

		// ignore the request if the current user doesn't have sufficient permissions
		if ( ! current_user_can( 'publish_masterslider' ) ) {
			return;
		}

		// Get the slider id
		$slider_id = isset( $_REQUEST['slider_id'] ) ? $_REQUEST['slider_id'] : '';

		if ( empty( $slider_id ) ) {
			return;
		}

		// get panel data
		$msp_data = isset( $_REQUEST['msp_data'] ) ? $_REQUEST['msp_data'] : null;

		// get parse and database tools
		global $mspdb;

		// store slider data in database
		$old_slider = $mspdb->get_slider( $slider_id );
		$old_params = json_decode( base64_decode( $old_slider['params'] ), true );
		$params     = json_decode( base64_decode( $msp_data ), true );

		if ( isset( $params['MSPanel.Slide'] ) ) {

			$slider_config = array(
				'info'      => array(),
				'bgAlt'     => array(),
				'bgTitle'   => array(),
				'linkTitle' => array()
			);

			foreach ( $params['MSPanel.Slide'] as $key => $slide ) {
				$slide = json_decode( $slide, true );
				if ( $old_params && is_array( $old_params ) && $old_params['MSPanel.Slide'] ) {
					foreach ( $old_params['MSPanel.Slide'] as $old_slide ) {
						$old_slide = json_decode( $old_slide, true );
						if ( $slide['id'] === $old_slide['id'] ) {
							$strings   = wpm_value_to_ml_array( $old_slide );
							$new_value = wpm_set_language_value( $strings, $slide, $slider_config );
							$slide     = wpm_ml_value_to_string( $new_value );
						}
					}
				} else {
					if ( ! wpm_is_ml_value( $slide ) ) {
						$slide = wpm_set_language_value( array(), $slide, $slider_config );
						$slide = wpm_ml_value_to_string( $slide );
					}
				}
				$params['MSPanel.Slide'][ $key ] = wp_json_encode( $slide );
			}
		}

		if ( isset( $params['MSPanel.Layer'] ) ) {

			$layer_config = array(
				'title'   => array(),
				'content' => array()
			);

			foreach ( $params['MSPanel.Layer'] as $key => $layer ) {
				$layer = json_decode( $layer, true );
				if ( $old_params && is_array( $old_params ) && $old_params['MSPanel.Layer'] ) {
					foreach ( $old_params['MSPanel.Layer'] as $old_layer ) {
						$old_layer = json_decode( $old_layer, true );
						if ( $layer['id'] === $old_layer['id'] ) {
							$strings   = wpm_value_to_ml_array( $old_layer );
							$new_value = wpm_set_language_value( $strings, $layer, $layer_config );
							$layer     = wpm_ml_value_to_string( $new_value );
						}
					}
				} else {
					if ( ! wpm_is_ml_value( $layer ) ) {
						$layer = wpm_set_language_value( array(), $layer, $layer_config );
						$layer = wpm_ml_value_to_string( $layer );
					}
				}
				$params['MSPanel.Layer'][ $key ] = wp_json_encode( $layer );
			}
		}

		$_REQUEST['msp_data'] = base64_encode( wp_json_encode( $params ) );
	}


	/**
	 * Translate slider
	 */
	public function translate_slider() {
		global $wp_scripts;

		$data        = $wp_scripts->get_data( 'jquery-core', 'data' );
		$data_array  = explode( "\n", $data );
		$slider_data = '';

		foreach ( $data_array as $key => $item ) {
			if ( strpos( $item, '__MSP_DATA' ) ) {
				$slider_data = str_replace( 'var __MSP_DATA = ', '', substr( $item, 0, - 1 ) );
				unset( $data_array[ $key ] );
				break;
			}
		}

		if ( $slider_data && $slider_data !== 'null' ) {
			$slider_data = json_decode( base64_decode( $slider_data ), true );

			if ( isset( $slider_data['MSPanel.Slide'] ) ) {
				foreach ( $slider_data['MSPanel.Slide'] as $key => $slide ) {
					$slide                                = json_decode( $slide, true );
					$slide                                = wpm_translate_value( $slide );
					$slider_data['MSPanel.Slide'][ $key ] = wp_json_encode( $slide );
				}
			}

			if ( isset( $slider_data['MSPanel.Layer'] ) ) {
				foreach ( $slider_data['MSPanel.Layer'] as $key => $layer ) {
					$layer                                = json_decode( $layer, true );
					$layer                                = wpm_translate_value( $layer );
					$slider_data['MSPanel.Layer'][ $key ] = wp_json_encode( $layer );
				}
			}

			$data_array[] = 'var __MSP_DATA = "' . base64_encode( wp_json_encode( $slider_data ) ) . '";';
			$data         = implode( "\n", $data_array );
			$wp_scripts->add_data( 'jquery-core', 'data', $data );
		}
	}


	/**
	 * Add language_switcher
	 *
	 * @param $config
	 *
	 * @return array
	 */
	public function add_language_switcher( $config ) {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		if ( $screen_id === 'toplevel_page_masterslider' && isset( $_GET['slider_id'] ) ) {
			$config[] = 'toplevel_page_masterslider';
		}

		return $config;
	}
}

new WPM_Masterslider();
