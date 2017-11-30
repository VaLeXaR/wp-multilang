<?php
/**
 * WP Multilang General Settings
 *
 * @category    Admin
 * @package     WPM/Admin
 * @author   Valentyn Riaboshtan
 */

namespace WPM\Includes\Admin\Settings;
use WPM\Includes\Admin\WPM_Admin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WPM_Settings_General.
 */
class WPM_Settings_General extends WPM_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'wp-multilang' );

		parent::__construct();

		add_filter( 'wpm_general_settings', array( $this, 'add_uninstall_setting' ) );
		add_action( 'wpm_update_options_general', array( $this, 'update_wplang' ) );
		add_action( 'wpm_admin_field_language_domains', array( $this, 'language_domains' ) );
		add_action( 'wpm_admin_settings_sanitize_option_wpm_language_domains', array( $this, 'sanitize_language_domains' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		wp_enqueue_script( 'wpm_general_settings' );

		$languages = wpm_get_languages();

		$language_options = array();
		foreach ( $languages as $code => $language ) {
			$language_options[ $code ] = $language['name'];
		}

		$settings = apply_filters( 'wpm_general_settings', array(

			array(
				'title' => __( 'General options', 'wp-multilang' ),
				'type'  => 'title',
				'desc'  => sprintf( __( 'Read <a href="%s" target="_blank">Google guidelines</a> before.', 'wp-multilang' ), esc_url( 'https://support.google.com/webmasters/answer/182192?hl=en' ) ),
				'id'    => 'general_options'
			),

			array(
				'title'    => __( 'Site Language', 'wp-multilang' ),
				'desc'     => __( 'Set default site language.', 'wp-multilang' ),
				'id'       => 'wpm_site_language',
				'default'  => wpm_get_default_language(),
				'type'     => 'select',
				'class'    => 'wpm-enhanced-select',
				'css'      => 'min-width: 350px;',
				'options'  => $language_options,
			),

			array(
				'title'   => __( 'Show untranslated', 'wp-multilang' ),
				'desc'    => __( 'Show untranslated strings on language by default.', 'wp-multilang' ),
				'id'      => 'wpm_show_untranslated_strings',
				'default' => 'yes',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Browser redirect', 'wp-multilang' ),
				'desc'    => __( 'Use redirect to user browser language in first time.', 'wp-multilang' ),
				'id'      => 'wpm_use_redirect',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array( 'type' => 'sectionend', 'id' => 'general_options' ),

			array(
				'title' => __( 'Mode', 'wp-multilang' ),
				'type'  => 'title',
				'desc'  => __( 'Set up url modification for the site.', 'wp-multilang' ),
				'id'    => 'mode_options'
			),

			array(
				'title' => __( 'URL Modification', 'wp-multilang' ),
				'id' => 'wpm_url_mode',
				'desc' => __( 'For separating languages by domains or sub-domains You will need to configure Yor server settings.', 'wp-multilang' ),
				'default' => 1,
				'type' => 'radio',
				'class' => 'wpm_url_mode',
				'options' => array(
					1 => sprintf( __( 'Pre path Mode (used %s)', 'wp-multilang' ), esc_url(  wpm_get_orig_home_url() . '/' . wpm_get_default_language() . '/' ) ),
					2 => sprintf( __( 'Pre domain Mode (used %s)', 'wp-multilang' ), esc_url( 'http://' . wpm_get_default_language() . '.' . str_replace( array( 'http://', 'https://' ), '', wpm_get_orig_home_url() ) ) ),
					3 => __( 'Per domain Mode: specify separate user-defined domain for each language', 'wp-multilang' ),
				),
			),

			array(
				'title' => __( 'Language domains', 'wp-multilang' ),
				'id'    => 'wpm_language_domains',
				'desc'  => __( 'Only for non default language.', 'wp-multilang' ),
				'type'  => 'language_domains',
				'class' => 'wpm_language_domains',
			),

			array(
				'title'   => __( 'Use prefix', 'wp-multilang' ),
				'desc'    => __( 'Use prefix for language by default.', 'wp-multilang' ),
				'id'      => 'wpm_use_prefix',
				'default' => 'no',
				'type'    => 'checkbox',
				'hide_if_checked' => get_option( 'wpm_url_mode', 1 ) == 3 ? 'yes' : 'no',
			),

			array( 'type' => 'sectionend', 'id' => 'mode_options' ),

		) );

		return apply_filters( 'wpm_get_settings_' . $this->id, $settings );
	}

	/**
	 * Add uninstall settings only for Super Admin
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function add_uninstall_setting( $settings ) {

		if ( ! is_multisite() || ( is_main_site() ) ) {
			$settings[] = array(
				'title' => __( 'Uninstalling', 'wp-multilang' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'uninstall_options',
			);

			$settings[] = array(
				'title'   => __( 'Delete translations', 'wp-multilang' ),
				'desc'    => __( 'Delete translations when uninstalling plugin (some translations may not be deleted and you must delete them manually).', 'wp-multilang' ),
				'id'      => 'wpm_uninstall_translations',
				'default' => 'no',
				'type'    => 'checkbox',
			);

			$settings[] = array( 'type' => 'sectionend', 'id' => 'uninstall_options' );
		}

		return $settings;
	}


	public function language_domains( $value ) {

		$languages = wpm_get_languages();
		$domains   = get_option( 'wpm_language_domains', array() );
		$url_mode  = get_option( 'wpm_url_mode', 1 );
		?>
		<tr valign="top" class="<?php esc_attr_e( $value['class'] ); ?>" <?php if ( $url_mode != 3 ) { ?>style="display: none;"<?php } ?>>
			<th scope="row" class="titledesc">
				<h4><?php echo esc_html( $value['title'] ); ?></h4>
			</th>
			<td class="forminp">
				<?php foreach ( $languages as $code => $language ) {
					if ( $code == wpm_get_default_language() ) {
						continue;
					}
					?>
					<p><input type="text" name="<?php esc_attr_e( $value['id'] ); ?>[<?php echo esc_attr( $code ); ?>]" title="<?php esc_attr_e( $language['name'] ) ?>" placeholder="<?php esc_attr_e( $language['name'] ) ?>" value="<?php echo esc_url( isset( $domains[ $code ] ) ? $domains[ $code ] : '' ); ?>" class="regular-text"></p>
				<?php } ?>
				<p class="description"><?php esc_html_e( $value['desc'] ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save settings.
	 */
	public function save() {

		$settings = $this->get_settings();

		WPM_Admin_Settings::save_fields( $settings );
	}


	public function sanitize_language_domains( $value ) {
		foreach ( $value as $key => $item ) {
			$item          = esc_url( $item );
			$item          = untrailingslashit( $item );
			$value[ $key ] = $item;
		}

		return $value;
	}

	/**
	 * Update WPLANG option
	 */
	public function update_wplang() {
		$value     = WPM_Admin_Settings::get_option( 'wpm_site_language' );
		$languages = wpm_get_languages();
		$locale    = $languages[ $value ]['translation'];
		update_option( 'WPLANG', 'en_US' !== $locale ? $locale : '' );
	}
}
