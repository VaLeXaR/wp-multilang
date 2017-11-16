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

			array( 'type' => 'sectionend', 'id' => 'additional_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}


	public function get_default_translation_field( $value ) {

		$main_params = array(
			'plugin_url'                 => wpm()->plugin_url(),
			'ajax_url'                   => admin_url( 'admin-ajax.php' ),
			'set_default_language_nonce' => wp_create_nonce( 'set-default-language' ),
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
				<input type="button" id="set_default_language" class="button" value="<?php printf(__( 'Set %s by default', 'wp-multilang' ), $languages[ $default_language ]['name'] ); ?>">
				<p class="description"><?php _e( 'Set default translation for all posts, taxonomies, fields and options that available in config and not be translated before.<br><strong>WARNING:</strong> Changes are not reversible. Make a backup of the site database before starting.', 'wp-multilang' ) ?></p>
			</td>
		</tr>
		<?php
	}
}
