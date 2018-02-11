<?php
/**
 * WP Multilang Additional Settings
 *
 * @category    Admin
 * @package     WPM/Admin
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Settings_General.
 */
class WPM_Settings_Additional extends WPM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'additional';
		$this->label = __( 'Additional', 'wp-multilang' );

		parent::__construct();

		add_action( 'wpm_admin_field_localizations', array( $this, 'localizations' ) );
		add_action( 'wpm_admin_field_qtx_import', array( $this, 'qtx_import' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		// Hide the save button
		$GLOBALS['hide_save_button'] = true;

		$settings = apply_filters( 'wpm_' . $this->id . '_settings', array(

			array( 'title' => __( 'Actions', 'wp-multilang' ), 'type' => 'title', 'desc' => '', 'id' => 'additional_options' ),

			array(
				'title' => __( 'Installed localizations', 'wp-multilang' ),
				'id'    => 'wpm_installed_localizations',
				'type'  => 'localizations',
			),

			array(
				'title' => __( 'qTranslate import', 'wp-multilang' ),
				'id'    => 'wpm_qtx_import',
				'type'  => 'qtx_import',
			),

			array( 'type' => 'sectionend', 'id' => 'additional_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}

	/**
	 * Output the settings.
	 */
	public function output() {

		$main_params = array(
			'plugin_url'                 => wpm()->plugin_url(),
			'ajax_url'                   => admin_url( 'admin-ajax.php' ),
			'delete_localization_nonce'  => wp_create_nonce( 'delete-localization' ),
			'qtx_import_nonce'           => wp_create_nonce( 'qtx-import' ),
			'confirm_question'           => __( 'Are you sure you want to delete this localization?', 'wp-multilang' ),
		);
		wp_localize_script( 'wpm_additional_settings', 'wpm_additional_settings_params', $main_params );
		wp_enqueue_script( 'wpm_additional_settings' );

		parent::output();
	}

	/**
	 * Get localizations field
	 *
	 * @param $value
	 */
	public function localizations( $value ) {

		$installed_localizations = wpm_get_installed_languages();
		$available_translations  = wpm_get_available_translations();
		$options                 = get_option( 'wpm_languages', array() );
		$active                  = 0;
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<h4><?php echo esc_html( $value['title'] ); ?></h4>
			</th>
			<td class="forminp">
				<p>
					<select id="<?php echo esc_attr( $value['id'] ); ?>" title="<?php esc_html_e( 'Installed localizations', 'wp-multilang' ); ?>">
						<?php foreach ( $installed_localizations as $localization ) { ?>
							<?php
							$used = false;
							foreach ( $options as $code => $language ) {
								if ( $language['translation'] == $localization ) {
									$used = true;
									break;
								}
							}

							if ( 'en_US' !== $localization && false == $used ) {
								$active++;
							}
							?>
							<option value="<?php esc_attr_e( $localization ); ?>" <?php disabled( ( 'en_US' == $localization ) || $used ); ?>><?php esc_attr_e( $available_translations[ $localization ]['native_name'] ); ?></option>
						<?php } ?>
					</select>
					<button type="button" id="delete_localization" class="button js-wpm-action" <?php disabled( 0 == $active ); ?>><?php esc_attr_e( 'Delete localization', 'wp-multilang' ); ?></button>
				</p>
				<p class="description"><?php esc_html_e( 'Delete unused not built-in language pack', 'wp-multilang' ); ?></p>
			</td>
		</tr>
		<?php

	}

	/**
	 * Import qTranslate taxonomies
	 *
	 * @param $value
	 */
	public function qtx_import( $value ) {

		$disabled = true;

		if ( $qts_translations = get_option( 'qtranslate_term_name' ) ) {
			$disabled = false;
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<h4><?php echo esc_html( $value['title'] ); ?></h4>
			</th>
			<td class="forminp">
				<p>
					<button type="button" id="qtx_import" class="button js-wpm-action" <?php disabled( $disabled ); ?>><?php esc_attr_e( 'Import', 'wp-multilang' ); ?></button>
				</p>
				<p class="description"><?php esc_html_e( 'Import names for terms from qTranslate.', 'wp-multilang' ); ?></p>
			</td>
		</tr>
		<?php
	}
}
