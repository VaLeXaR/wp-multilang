<?php
/**
 * WP Multilang Languages Settings
 *
 * @category    Admin
 * @package     WPM/Admin
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes\Admin\Settings;
use WPM\Includes\Admin\WPM_Admin_Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Settings_General.
 */
class WPM_Settings_Languages extends WPM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'languages';
		$this->label = __( 'Languages', 'wp-multilang' );

		parent::__construct();

		add_action( 'wpm_admin_field_languages', array( $this, 'get_languages' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters( 'wpm_' . $this->id . '_settings', array(

			array( 'title' => __( 'Languages', 'wp-multilang' ), 'type' => 'title', 'desc' => '', 'id' => 'languages_options' ),

			array(
				'title' => __( 'Installed languages', 'wp-multilang' ),
				'id'    => 'wpm_languages',
				'type'  => 'languages',
			),

			array( 'type' => 'sectionend', 'id' => 'languages_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}

	/**
	 * Get languages field
	 *
	 * @param $value
	 */
	public function get_languages( $value ) {

		$main_params = array(
			'plugin_url'                => wpm()->plugin_url(),
			'flags_dir'                 => wpm_get_flags_dir(),
			'ajax_url'                  => admin_url( 'admin-ajax.php' ),
			'delete_lang_nonce'         => wp_create_nonce( 'delete-lang' ),
			'confirm_question'          => __( 'Are you sure you want to delete this language?', 'wp-multilang' ),
		);
		wp_localize_script( 'wpm_languages', 'wpm_languages_params', $main_params );

		wp_enqueue_script( 'wpm_languages' );
		wp_enqueue_style( 'select2' );

		$languages = get_option( 'wpm_languages', array() );
		$flags     = wpm_get_flags();

		include_once __DIR__ . '/views/html-languages.php';
	}

	/**
	 * Save settings.
	 */
	public function save() {

		if ( $value = wpm_get_post_data_by_key( 'wpm_languages' ) ) {

			$option_name         = 'wpm_languages';
			$languages           = array();
			$error               = false;
			$translations        = wpm_get_available_translations();
			$installed_languages = wpm_get_installed_languages();

			foreach ( $installed_languages as $installed_language ) {
				if ( isset( $translations[ $installed_language ] ) ) {
					unset( $translations[ $installed_language ] );
				}
			}

			foreach ( $value as $item ) {

				if ( empty( $item['code'] ) || empty( $item['locale'] ) ) {
					$error = true;
					break;
				}

				$code = wpm_sanitize_lang_slug( $item['code'] );

				if ( ! $code ) {
					$error = true;
					break;
				}

				$languages[ $code ] = array(
					'enable'      => $item['enable'] ? 1 : 0,
					'locale'      => $item['locale'],
					'name'        => $item['name'],
					'translation' => $item['translation'] ?: 'en_US',
					'date'        => $item['date'],
					'time'        => $item['time'],
					'flag'        => $item['flag'],
				);

				if ( isset( $translations[ $item['translation'] ] ) && wp_can_install_language_pack() && current_user_can( 'install_languages' ) ) {
					wp_download_language_pack( $item['translation'] );
					WPM_Admin_Notices::add_custom_notice(
						$option_name . '_lang_pack_installed',
						__( 'New language pack successfully installed', 'wp-multilang' )
					);
				}
			}

			if ( $error ) {
				return;
			}

			$languages = apply_filters( 'wpm_save_languages', $languages, $value );
			$locale    = $languages[ wpm_get_default_language() ]['translation'];
			update_option( 'WPLANG', 'en_US' !== $locale ? $locale : '' );
			$user_locale = $languages[ wpm_get_user_language() ]['translation'];
			update_user_meta( get_current_user_id(), 'locale', $user_locale );


			update_option( $option_name, $languages );
		}// End if().
	}
}
