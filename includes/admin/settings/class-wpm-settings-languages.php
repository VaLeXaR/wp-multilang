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
		add_action( 'wpm_admin_field_localizations', array( $this, 'get_localizations' ) );
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
				'title'    => __( 'Installed languages', 'wp-multilang' ),
				'desc'     => '',
				'id'       => 'wpm_languages',
				'default'  => '',
				'type'     => 'languages',
				'css'      => '',
			),

			array(
				'title'    => __( 'Installed localizations', 'wp-multilang' ),
				'desc'     => '',
				'id'       => 'wpm_installed_localizations',
				'default'  => '',
				'type'     => 'localizations',
				'css'      => '',
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
			'delete_localization_nonce' => wp_create_nonce( 'delete-localization' ),
			'confirm_question'          => __( 'Are you sure you want to delete this language?', 'wp-multilang' ),
		);
		wp_localize_script( 'wpm_languages', 'wpm_languages_params', $main_params );

		wp_enqueue_script( 'wpm_languages' );
		wp_enqueue_style( 'select2' );

		$languages = get_option( 'wpm_languages', array() );
		$flags     = wpm_get_flags();

		include_once( dirname( __FILE__ ) . '/views/html-languages.php' );
	}

	/**
	 * Get localizations field
	 *
	 * @param $value
	 */
	public function get_localizations( $value ) {

		$installed_localizations = wpm_get_installed_languages();
		$available_translations  = wpm_get_available_translations();
		$options                 = get_option( 'wpm_languages', array() );
		$button = 0;
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp">
				<p>
					<select id="<?php echo esc_attr( $value['id'] ); ?>" title="<?php esc_html_e( 'Installed localizations', 'wp-multilang' ); ?>">
						<?php foreach ( $installed_localizations as $localization ) { ?>
							<?php
							$used = false;
							foreach ( $options as $lang => $language ) {
								if ( $language['translation'] == $localization ) {
									$used = true;
									break;
								}
								$button++;
							}
							?>
							<option value="<?php esc_attr_e( $localization ); ?>" <?php disabled( ( 'en_US' == $localization ) || $used ); ?>><?php esc_attr_e( $available_translations[ $localization ]['native_name'] ); ?></option>
						<?php } ?>
					</select>
					<input type="button" id="delete_localization" class="button" value="<?php esc_attr_e( 'Delete localization', 'wp-multilang' ); ?>" <?php disabled( $button == 0 ); ?>>
				</p>
				<p><?php esc_html_e( 'Delete unused not built-in language pack', 'wp-multilang' ); ?></p>
			</td>
		</tr>
		<?php

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

				if ( empty( $item['slug'] ) || empty( $item['locale'] ) ) {
					$error = true;
					break;
				}

				$slug = wpm_sanitize_lang_slug( $item['slug'] );

				if ( ! $slug ) {
					$error = true;
					break;
				}

				$languages[ $slug ] = array(
					'enable'      => $item['enable'] ? 1 : 0,
					'locale'      => $item['locale'],
					'name'        => $item['name'],
					'translation' => $item['translation'] ? $item['translation'] : 'en_US',
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

			update_option( $option_name, $languages );
		}// End if().
	}
}
