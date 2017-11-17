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

		add_action( 'wpm_admin_field_set_default_translation', array( $this, 'get_default_translation_field' ) );
		add_action( 'wpm_admin_field_localizations', array( $this, 'get_localizations' ) );
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
				'title'    => __( 'Set default translation', 'wp-multilang' ),
				'desc'     => '',
				'id'       => 'wpm_set_default_translation',
				'default'  => '',
				'type'     => 'set_default_translation',
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

			array( 'type' => 'sectionend', 'id' => 'additional_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}

	/**
	 * Set default translation field
	 *
	 * @param $value
	 */
	public function get_default_translation_field( $value ) {

		$main_params = array(
			'plugin_url'                 => wpm()->plugin_url(),
			'ajax_url'                   => admin_url( 'admin-ajax.php' ),
			'set_default_language_nonce' => wp_create_nonce( 'set-default-language' ),
			'delete_localization_nonce'  => wp_create_nonce( 'delete-localization' ),
			'confirm_question'           => __( 'Are you sure you want to delete this localization?', 'wp-multilang' ),
		);
		wp_localize_script( 'wpm_additional_settings', 'wpm_additional_settings_params', $main_params );
		wp_enqueue_script( 'wpm_additional_settings' );

		$languages        = wpm_get_languages();
		$default_language = wpm_get_default_language();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<h4><?php echo esc_html( $value['title'] ); ?></h4>
			</th>
			<td class="forminp">
				<button type="button" id="set_default_language" class="button js-set_default_language"><?php printf( __( 'Set <strong>%s</strong> by default', 'wp-multilang' ), $languages[ $default_language ]['name'] ); ?></button>
				<p class="description"><?php _e( 'Set default translation for all posts, taxonomies, fields and options that available in config and not be translated before.<br><strong>WARNING:</strong> Changes are not reversible. Make a backup of the site database before starting.', 'wp-multilang' ) ?></p>
			</td>
		</tr>
		<?php
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
		$active                  = 0;
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
							}

							if ( 'en_US' !== $localization && false == $used ) {
								$active++;
							}
							?>
							<option value="<?php esc_attr_e( $localization ); ?>" <?php disabled( ( 'en_US' == $localization ) || $used ); ?>><?php esc_attr_e( $available_translations[ $localization ]['native_name'] ); ?></option>
						<?php } ?>
					</select>
					<button type="button" id="delete_localization" class="button js-delete_localization" <?php disabled( 0 == $active ); ?>><?php esc_attr_e( 'Delete localization', 'wp-multilang' ); ?></button>
				</p>
				<p><?php esc_html_e( 'Delete unused not built-in language pack', 'wp-multilang' ); ?></p>
			</td>
		</tr>
		<?php

	}
}
