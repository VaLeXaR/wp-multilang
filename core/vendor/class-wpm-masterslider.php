<?php
/**
 * Class for capability with Contact Form 7
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'MSWP_AVERTA_VERSION' ) ) {

	/**
	 * Class WPM_CF7
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @since    1.2.0
	 */
	class WPM_Masterslider {

		/**
		 * WPM_CF7 constructor.
		 */
		public function __construct() {
//			add_action( 'admin_enqueue_scripts', array( $this, 'add_translator_script' ), 11 );
			add_action('wp_ajax_msp_panel_handler', function(){

				// verify nonce
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], "msp_panel") ) {
					return;
				}

				// ignore the request if the current user doesn't have sufficient permissions
				if ( ! current_user_can( 'publish_masterslider' ) ) {
					return;
				}

				// Get the slider id
				$slider_id 		= isset( $_REQUEST['slider_id'] ) ? $_REQUEST['slider_id'] : '';

				if ( empty( $slider_id ) ) {
					return;
				}

				// get panel data
				$msp_data		= isset( $_REQUEST['msp_data']      ) ? $_REQUEST['msp_data']      : NULL;

				// get parse and database tools
				global $mspdb;


				// store slider data in database
				$old_slider = $mspdb->get_slider( $slider_id );

//				s(json_decode( base64_decode( $msp_data), true), $old_slider);
//				die();
			}, 0);

			add_action( 'masterslider_admin_add_panel_variables', array( $this, 'translate_slider' ) );
		}


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

			if ( $slider_data ) {
				$slider_data = json_decode( base64_decode( $slider_data ), true );

				foreach ( $slider_data['MSPanel.Layer'] as $key => $layer ) {
					$layer                                = json_decode( $layer, true );
					$layer                                = wpm_translate_value( $layer );
					$slider_data['MSPanel.Layer'][ $key ] = json_encode( $layer );
				}

				foreach ( $slider_data['MSPanel.Slide'] as $key => $slide ) {
					$slide                                = json_decode( $slide, true );
					$slide                                = wpm_translate_value( $slide );
					$slider_data['MSPanel.Slide'][ $key ] = json_encode( $slide );
				}

				$data_array[] = 'var __MSP_DATA = "' . base64_encode( json_encode( $slider_data ) ) . '";';
				$data         = implode( "\n", $data_array );
				$wp_scripts->add_data( 'jquery-core', 'data', $data );
			}
		}


		/**
		 * Translate some field without PHP filters by javascript for displaying
		 */
		public function add_translator_script() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( $screen_id == 'toplevel_page_wpcf7' && isset( $_GET['post'] ) ) {
				wp_enqueue_script( 'wpm_translator' );
				wpm_enqueue_js( "
					(function ( $ ) {
						$( '#title' ).each( function () {
							var text = wpm_translator.translate_string($(this).val());
							$(this).val(text);
						} );
					})( window.jQuery );
				" );
			}
		}
	}

	new WPM_Masterslider();
}
