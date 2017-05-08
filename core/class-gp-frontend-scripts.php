<?php
/**
 * Handle frontend scripts
 *
 * @class       QtN_Frontend_Scripts
 * @version     2.3.0
 * @package     GamePortal/Classes/
 * @category    Class
 * @author      VaLeXaR
 */

namespace QtNext;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QTN_Frontend_Scripts Class.
 */
class QtN_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by QtN.
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by QtN.
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by QtN.
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend.
	 * @access private
	 * @return array
	 */
	public static function get_styles() {
		$styles = array(
			/*'game-portal-general' => array(
				'src'     => qtn_asset_path( 'css/main.css' ),
				'deps'    => '',
				'version' => QTN_VERSION,
				'media'   => 'all'
			),*/
		);

		return $styles;
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = QTN_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = QTN_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = QTN_VERSION, $media = 'all' ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @access private
	 *
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = QTN_VERSION, $media = 'all' ) {
		if ( ! in_array( $handle, self::$styles ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {

		if ( ! did_action( 'before_qtranslate_next_init' ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Register any scripts for later use, or used as dependencies
		/*self::register_script( 'gp-bootstrap', gp_asset_path( 'js/vendor/bootstrap/bootstrap' . $suffix . '.js' ), array( 'jquery' ) );
		self::register_script( 'gp-blueimp-gallery', gp_asset_path( 'js/vendor/blueimp-gallery/blueimp-gallery' . $suffix . '.js' ), array( 'jquery' ) );
		self::register_script( 'gp-download', gp_asset_path( 'js/vendor/download/download' . $suffix . '.js' ) );
		self::register_script( 'gp-owl-carousel', gp_asset_path( 'js/vendor/owl-carousel/owl-carousel' . $suffix . '.js' ), array( 'jquery' ) );
		self::register_script( 'gp-jquery-print', gp_asset_path( 'js/vendor/jquery-print/jQuery.print' . $suffix . '.js' ), array( 'jquery' ) );
		self::register_script( 'gp-google-maps', '//maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . get_option( 'game_portal_google_api_key' ) . '', array(), null );

		// Global frontend scripts
		self::enqueue_script( 'game-portal', gp_asset_path( 'js/scripts.js' ), array(
			'jquery-ui-core',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-mouse',
			'jquery-ui-draggable',
			'jquery-ui-sortable',
			'jquery-ui-datepicker',
			'underscore',
			'gp-owl-carousel',
			'gp-blueimp-gallery',
			'gp-bootstrap',
			'gp-jquery-print',
			'gp-download'
		) );
		wp_localize_jquery_ui_datepicker();

		self::enqueue_script( 'gp-google-maps' );*/


		// CSS Styles
		if ( $enqueue_styles = self::get_styles() ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				self::enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
		}
	}

	/**
	 * Localize a QtN script once.
	 * @access private
	 *
	 * @param  string $handle
	 */
	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_data( $handle ) ) ) {
			$name                        = str_replace( '-', '_', $handle ) . '_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 * @access private
	 *
	 * @param  string $handle
	 *
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {

		switch ( $handle ) {
			case 'game-portal' :

				/*$default = array(
					'ajax_url'       => QN()->ajax_url(),
					'gp_ajax_url'    => QTN_AJAX::get_endpoint( "%%endpoint%%" ),
					'html_templates' => array(
						'modals' => gp_get_template_html( 'jquery-templates/modals.tpl' ),
					)
				);

				$data = array();

				if ( is_edit_level() || is_edit_game() ) {
					$data = array(
						'current_user_id'    => get_current_user_id(),
						'audio_upload_nonce' => wp_create_nonce( 'audio-upload' ),
						'image_upload_nonce' => wp_create_nonce( 'image-upload' ),
						'post_delete_nonce'  => wp_create_nonce( 'post-delete' ),
						'get_qr_code_nonce'  => wp_create_nonce( 'get-qr-code' ),
						'max_upload_size'    => wp_max_upload_size(),
						'date_format'        => gp_dateformat_PHP_to_jQueryUI( get_option( 'date_format' ) ),
						'media_rest_url'     => get_rest_url( null, '/wp/v2/media' ),
						'map_cursor'         => gp_asset_path( 'images/pictures/aim.png' ),
						'html_templates'     => array(
							'new_audio' => gp_get_template_html( 'jquery-templates/audio-item.tpl' ),
							'image'     => gp_get_template_html( 'jquery-templates/image-item.tpl' ),
						),
					);
				}

				if ( is_purchase() ) {
					$data = array(
						'pay_add_materials_nonce' => wp_create_nonce( 'pay-add-materials' ),
						'use_coupon_nonce'        => wp_create_nonce( 'use-coupon' ),
						'paypall_action_nonce'    => wp_create_nonce( 'paypall-action' ),
						'text_pay_agreement'      => __( 'You must agree with the General Terms and Conditions', 'game-portal' ),

					);
				}

				if ( is_share() ) {
					$data = array(
						'inviting_to_game_nonce' => wp_create_nonce( 'inviting-to-game' ),
						'send_answers_nonce'     => wp_create_nonce( 'send-answers' ),
					);
				}

				if ( is_account_page() ) {
					$data = array(
						'game_delete_nonce'    => wp_create_nonce( 'game-delete' ),
						'game_copy_nonce'      => wp_create_nonce( 'game-copy' ),
						'check_new_game_nonce' => wp_create_nonce( 'check-new-game' ),
						'text_game_is_copying' => __( 'Game is being created ... It can take up to 1 minute until it will appear in your account', 'game-portal' ),
						'html_templates'       => array(
							'new_game' => gp_get_template_html( 'jquery-templates/game-item.tpl' ),
						)
					);
				}

				if ( is_level() && ! is_edit_level() ) {
					$level = gp_get_level();
					$data  = array(
						'get_hint_nonce'   => wp_create_nonce( 'get-hint' ),
						'get_answer_nonce' => wp_create_nonce( 'get-answer' ),
						'audio_auto_play'  => $level->level_audio_auto_play,
						'audio_once'       => $level->level_audio_once,
						'video_auto_play'  => $level->level_video_auto_play,
						'video_mute'       => $level->level_video_mute,
						'html_templates'   => array(
							'answer' => gp_get_template_html( 'jquery-templates/answer.tpl' ),
						)
					);
				}

				return array_merge_recursive( $default, $data );*/
				break;
		}

		return false;
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}
