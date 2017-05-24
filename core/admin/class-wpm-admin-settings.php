<?php
/**
 * WPM Admin Settings Class
 *
 * @author   VaLeXaR
 * @category Admin
 * @package  WPM/Core/Admin
 */

namespace WPM\Core\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_Admin_Settings Class.
 */
class WPM_Admin_Settings {

	/**
	 * WPM_Admin_Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_section' ) );

	}

	/**
	 * Add settings section to general options page
	 */
	public function add_section() {

		add_settings_field(
			'wpm_switch_locale',
			'',
			array( $this, 'switch_locale' ),
			'general'
		);

		add_settings_section( 'wpm_options', __( 'Multilingual Settings', 'wpm' ), array(
			$this,
			'view_settings'
		), 'general' );

		register_setting( 'general', 'wpm_languages', array(
			'type'              => 'array',
			'group'             => 'general',
			'description'       => '',
			'sanitize_callback' => array( $this, 'save_options' ),
			'show_in_rest'      => true,
		) );

		register_setting( 'general', 'wpm_uninstall_translations', array(
			'type'         => 'integer',
			'group'        => 'general',
			'description'  => __( 'Delete translations when uninstalling plugin', 'wpm' ),
			'show_in_rest' => true,
		) );
	}


	/**
	 * Fix for display default locale
	 */
	public function switch_locale() {
		switch_to_locale( wpm_get_default_locale() );
	}


	/**
	 * Display WPM options
	 */
	public function view_settings() {

		$_languages = array_flip( wpm_get_languages() );
		switch_to_locale( $_languages[ wpm_get_user_language() ] );

		$options             = wpm_get_options();
		$installed_languages = wpm_get_installed_languages();
		$languages           = apply_filters( 'wpm_languages', $options );
		$languages           = array_map( array( $this, 'set_default_settings' ), $languages );

		$flags    = array();
		$flag_dir = WPM()->plugin_path() . '/flags/';
		if ( $dir_handle = @opendir( $flag_dir ) ) {
			while ( false !== ( $file = readdir( $dir_handle ) ) ) {
				if ( preg_match( "/\.(jpeg|jpg|gif|png|svg)$/i", $file ) ) {
					$flags[] = $file;
				}
			}
			sort( $flags );
		}
		?>
		<table id="wpm-languages" class="wpm-languages widefat">
			<thead>
			<tr>
				<th class="wpm-lang-order"><?php esc_attr_e( 'Order', 'wpm' ); ?></th>
				<th class="wpm-lang-status"><?php esc_attr_e( 'Enable', 'wpm' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Locale', 'wpm' ); ?></th>
				<th class="wpm-lang-slug"><?php esc_attr_e( 'Slug *', 'wpm' ); ?></th>
				<th class="wpm-lang-name"><?php esc_attr_e( 'Name', 'wpm' ); ?></th>
				<th class="wpm-lang-flag"><?php esc_attr_e( 'Flag', 'wpm' ); ?></th>
				<th class="wpm-lang-delete"><?php esc_attr_e( 'Delete', 'wpm' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php $i = 1;
			foreach ( $languages as $key => $language ) { ?>
				<?php if ( ! is_string( $key ) ) {
					continue;
				} ?>
				<tr>
					<td class="wpm-lang-order"><?php echo $i; ?></td>
					<td class="wpm-lang-status">
						<input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="0">
						<input name="wpm_languages[<?php echo $key; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'wpm' ); ?>"<?php if ( $key == wpm_get_default_locale() ) { ?> disabled="disabled"<?php } ?>>
						<?php if ( $key == wpm_get_default_locale() ) { ?>
							<input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="1">
						<?php } ?>
					</td>
					<td class="wpm-lang-locale">
						<?php if ( in_array( $key, $installed_languages ) ) { ?>
							<?php esc_attr_e( $key ); ?>
						<?php } else { ?>
							<input type="text" name="wpm_languages[<?php echo $key; ?>][locale]"
							       value="<?php esc_attr_e( $key ); ?>"
							       title="<?php esc_attr_e( 'Locale', 'wpm' ); ?>"
							       placeholder="<?php esc_attr_e( 'Locale', 'wpm' ); ?>">
						<?php } ?>
					</td>
					<td class="wpm-lang-slug"><input type="text" name="wpm_languages[<?php echo $key; ?>][slug]"
					                                 value="<?php esc_attr_e( $language['slug'] ); ?>"
					                                 title="<?php esc_attr_e( 'Slug *', 'wpm' ); ?>"
					                                 placeholder="<?php esc_attr_e( 'Slug *', 'wpm' ); ?>" required>
					</td>
					<td class="wpm-lang-name"><input type="text" name="wpm_languages[<?php echo $key; ?>][name]"
					                                 value="<?php esc_attr_e( $language['name'] ); ?>"
					                                 title="<?php esc_attr_e( 'Name', 'wpm' ); ?>"
					                                 placeholder="<?php esc_attr_e( 'Name', 'wpm' ); ?>"></td>
					<td class="wpm-lang-flag">
						<select class="wpm-flags" name="wpm_languages[<?php echo $key; ?>][flag]"
						        title="<?php esc_attr_e( 'Flag', 'wpm' ); ?>">
							<option value=""><?php _e( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $flags as $flag ) { ?>
								<option
									value="<?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?>"<?php selected( $language['flag'], pathinfo( $flag, PATHINFO_FILENAME ) ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
							<?php } ?>
						</select>
						<?php if ( ( $language['flag'] ) ) { ?>
							<img src="<?php echo WPM()->flag_dir() . $language['flag'] . '.png'; ?>"
							     alt="<?php esc_attr_e( $language['name'] ); ?>">
						<?php } ?>
					</td>
					<td class="wpm-lang-delete">
						<?php if ( $key == get_locale() ) { ?>
							<?php _e( 'Current', 'wpm' ); ?>
						<?php } elseif ( $key == wpm_get_default_locale() ) { ?>
							<?php _e( 'Default', 'wpm' ); ?>
						<?php } elseif ( 'en_US' == $key ) { ?>
							<?php _e( 'Built-in', 'wpm' ); ?>
						<?php } else { ?>
							<button type="button" class="button button-link delete-language"
							        data-locale="<?php echo $key; ?>"><?php esc_attr_e( 'Delete', 'wpm' ); ?></button>
						<?php } ?>
					</td>
				</tr>
				<?php $i ++;
			} ?>
			</tbody>
			<tfoot>
			<tr>
				<th class="wpm-lang-order"><?php esc_attr_e( 'Order', 'wpm' ); ?></th>
				<th class="wpm-lang-status"><?php esc_attr_e( 'Enable', 'wpm' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Locale', 'wpm' ); ?></th>
				<th class="wpm-lang-slug"><?php esc_attr_e( 'Slug *', 'wpm' ); ?></th>
				<th class="wpm-lang-name"><?php esc_attr_e( 'Name', 'wpm' ); ?></th>
				<th class="wpm-lang-flag"><?php esc_attr_e( 'Flag', 'wpm' ); ?></th>
				<th class="wpm-lang-delete"><?php esc_attr_e( 'Delete', 'wpm' ); ?></th>
			</tr>
			</tfoot>
		</table>

		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Uninstalling', 'wpm' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text">
							<span><?php _e( 'Uninstalling', 'wpm' ); ?></span>
						</legend>
						<label for="wpm_uninstall_translations">
							<input type="hidden" name="wpm_uninstall_translations" value="0">
							<input name="wpm_uninstall_translations" type="checkbox" id="wpm_uninstall_translations"
							       value="1"<?php checked( get_option( 'wpm_uninstall_translations' ) ); ?>>
							<?php _e( 'Delete translations when uninstalling plugin', 'wpm' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<?php
	}

	/**
	 * Save WPM options
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function save_options( $value ) {

		$option_name = 'wpm_languages';
		$type        = '';

		$languages = array();

		foreach ( $value as $key => $item ) {

			$locale = $key;

			if ( isset( $item['locale'] ) ) {
				$locale = wpm_clean( $item['locale'] );
			}

			if ( empty( $item['slug'] ) ) {
				$type = 'error';
				break;
			}

			$languages[ $locale ] = array(
				'enable' => $_POST['wpm_languages'][ $key ]['enable'] ? 1 : 0,
				'slug'   => wpm_clean( $item['slug'] ),
				'name'   => wpm_clean( $item['name'] ),
				'flag'   => wpm_clean( $item['flag'] )
			);

			$translations        = wpm_get_translations();
			$installed_languages = wpm_get_installed_languages();

			foreach ( $installed_languages as $installed_language ) {
				unset( $translations[ $installed_language ] );
			}

			if ( in_array( $locale, $translations ) ) {
				if ( wp_can_install_language_pack() ) {
					wp_download_language_pack( $locale );
					$type = 'updated';
				}
			}
		}

		if ( 'updated' == $type ) {
			add_settings_error( $option_name, '', __( 'New language package installed', 'wpm' ), $type );
		}

		if ( 'error' == $type ) {
			add_settings_error( $option_name, '', __( 'Language slug is required', 'wpm' ), $type );

			return get_option( $option_name );

		}

		return $languages;
	}

	/**
	 * Set default language params
	 *
	 * @param $language
	 *
	 * @return array
	 */
	private function set_default_settings( $language ) {

		$default = array(
			'name'   => '',
			'slug'   => '',
			'flag'   => '',
			'enable' => 1
		);

		return array_merge( $default, $language );
	}
}
