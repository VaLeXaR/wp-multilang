<?php
/**
 * Class for capability with Visual Composer
 */

namespace WPM\Core\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WPB_VC_VERSION' ) ) {

	/**
	 * Class WPM_VC
	 * @package  WPM\Core\Vendor
	 * @category Vendor
	 * @author   VaLeXaR
	 * @since    1.1.0
	 */
	class WPM_VC {

		/**
		 * WPM_VC constructor.
		 */
		public function __construct() {
			add_action( 'vc_frontend_editor_render', array( $this, 'enqueue_js_frontend' ) );
			add_filter( 'vc_frontend_editor_iframe_url', array( $this, 'append_lang_to_url' ) );

			add_filter( 'vc_nav_front_controls', array( $this, 'nav_controls_frontend' ) );

			if ( ! vc_is_frontend_editor() ) {
				add_filter( 'vc_get_inline_url', array( $this, 'render_edit_button_link' ) );
			}
		}

		public function append_lang_to_url( $link ) {
			return add_query_arg( 'lang', wpm_get_language(), $link );
		}

		public function enqueue_js_frontend() {
			wpm_enqueue_js( "
				(function ( $ ) {
					$( '#vc_vendor_wpm_langs_front' ).change( function () {
						vc.closeActivePanel();
						$( '#vc_logo' ).addClass( 'vc_ui-wp-spinner' );
						window.location.href = $( this ).val();
					} );
					
					var nativeGetContent = vc.ShortcodesBuilder.prototype.getContent;
					vc.ShortcodesBuilder.prototype.getContent = function () {
						var content = nativeGetContent();
						jQuery( '#content' ).val( content );
						return content;
					};
				
				})( window.jQuery );
			" );
		}

		/**
		 * @return string
		 */
		public function generate_select_frontend() {
			$output             = '';
			$output             .= '<select id="vc_vendor_wpm_langs_front" class="vc_select vc_select-navbar">';
			$inline_url         = vc_frontend_editor()->getInlineUrl();
			$activeLanguage     = wpm_get_language();
			$options            = wpm_get_options();
			$availableLanguages = wpm_get_languages();
			foreach ( $availableLanguages as $locale => $lang ) {
				$output .= '<option value="' . add_query_arg( 'edit_lang', $lang, $inline_url ) . '" ' . selected( $lang, $activeLanguage, false ) . ' >' . $options[ $locale ]['name'] . '</option >';
			}
			$output .= '</select >';

			return $output;
		}

		/**
		 * @param $list
		 *
		 * @return array
		 */
		public function nav_controls_frontend( $list ) {
			if ( is_array( $list ) ) {
				$list[] = array(
					'wpm',
					'<li class="vc_pull-right" > ' . $this->generate_select_frontend() . '</li > ',
				);
			}

			return $list;
		}

		/**
		 * @param $link
		 *
		 * @return string
		 */
		public function render_edit_button_link( $link ) {
			return add_query_arg( 'edit_lang', wpm_get_language(), $link );
		}
	}

	new WPM_VC();
}
