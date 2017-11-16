<?php
/**
 * WP Multilang Languages Settings
 *
 * @category    Admin
 * @package     WPM/Admin
 */

namespace WPM\Includes\Admin\Settings;
use WPM\Includes\Admin\WPM_Admin_Notices;

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

		$settings = apply_filters( 'wpm_' . $this->id . '_settings', array(

			array( 'title' => __( 'Actions', 'wp-multilang' ), 'type' => 'title', 'desc' => '', 'id' => 'additional_options' ),

			array(
				'title'    => __( 'Set default translation', 'wp-multilang' ),
				'desc'     => __( 'Set default translation for all posts, taxonomies, options, meta fields', 'wp-multilang' ),
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

		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp">
				<input type="button" id="set_default_language" class="button button-primary" value="<?php esc_attr_e( 'Set default translation', 'wp-multilang' ); ?>">
			</td>
		</tr>
		<?php
	}
}
