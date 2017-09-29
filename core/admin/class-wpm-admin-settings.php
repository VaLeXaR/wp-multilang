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
 *
 * @version 1.0.1
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

		add_settings_section( 'wpm_setting_section', __( 'Multilingual Settings', 'wpm' ),  array( $this, 'view_settings' ), 'general' );
		add_settings_field( 'wpm_site_language', __( 'Site Language' ), array( $this, 'site_language_setting' ), 'general', 'wpm_setting_section' );

		add_settings_field( 'wpm_languages', __( 'Languages', 'wpm' ), array( $this, 'languages_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_languages', array(
			'type'              => 'array',
			'group'             => 'general',
			'description'       => __( 'Multilingual Settings', 'wpm' ),
			'sanitize_callback' => array( $this, 'save_languages' ),
			'show_in_rest'      => true,
		) );

		add_settings_field( 'wpm_show_untranslated_strings', __( 'Translating settings', 'wpm' ), array( $this, 'translating_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_show_untranslated_strings', array(
			'type'         => 'integer',
			'group'        => 'general',
			'description'  => __( 'Show untranslated strings', 'wpm' ),
			'show_in_rest' => true,
		) );

		if ( ! is_multisite() || ( is_main_site() ) ) {
			add_settings_field( 'wpm_uninstall_translations', __( 'Uninstalling', 'wpm' ), array( $this, 'uninstalling_setting' ), 'general', 'wpm_setting_section' );
			register_setting( 'general', 'wpm_uninstall_translations', array(
				'type'         => 'integer',
				'group'        => 'general',
				'description'  => __( 'Delete translations when uninstalling plugin', 'wpm' ),
				'show_in_rest' => true,
			) );
		}
	}


	/**
	 * Display WPM options
	 */
	public function view_settings() {
		wp_nonce_field( 'wpm_save_settings', 'wpm_save_settings_nonce' );
	}

	/**
	 * Show site language from DB
	 */
	public function site_language_setting() {
		$languages      = wpm_get_options();
		$translations   = wp_get_available_translations();
		$enable_locales = array();

		foreach ( $languages as $key => $language ) {
			if ( ! isset( $translations[ $key ] ) &&  'en_US' !== $key ) {
				$translations[ $key ] = array(
					'language'    => $key,
					'native_name' => $language['name'],
					'iso'         => array( $language['slug'] ),
				);
			}

			if ( $language['enable'] && 'en_US' !== $key ) {
				$enable_locales[] = $key;
			}
		}

		$locale = wpm_get_default_locale();
		if ( ! in_array( $locale, $enable_locales ) ) {
			$locale = '';
		}

		wp_dropdown_languages( array(
			'name'         => 'WPLANG',
			'id'           => 'wpm-install-language',
			'selected'     => $locale,
			'languages'    => $enable_locales,
			'translations' => $translations,
			'show_available_translations' => ( ! is_multisite() || is_super_admin() ) && wp_can_install_language_pack(),
		) );
	}

	/**
	 * Display languages
	 */
	public function languages_setting() {

		$languages           = wpm_get_options();
		$installed_languages = wpm_get_installed_languages();
		$new_languages       = apply_filters( 'wpm_languages', array() );

		$default = array(
			'name'   => __( 'No name', 'wpm' ),
			'slug'   => '',
			'flag'   => '',
			'enable' => 1,
		);

		foreach ( $new_languages as $new_locale => $new_language ) {
			if ( ! isset( $languages[ $new_locale ] ) ) {
				$languages[ $new_locale ] = wp_parse_args( $new_language, $default );
			}
		}

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
					<td class="wpm-lang-order"><?php esc_attr_e( $i ); ?></td>
					<td class="wpm-lang-status">
						<input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="0">
						<input name="wpm_languages[<?php echo $key; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'wpm' ); ?>"<?php if ( wpm_get_default_locale() === $key ) { ?> disabled="disabled"<?php } ?>>
						<?php if ( $key === wpm_get_default_locale() ) { ?>
							<input type="hidden" name="wpm_languages[<?php echo $key; ?>][enable]" value="1">
						<?php } ?>
					</td>
					<td class="wpm-lang-locale">
						<?php if ( in_array( $key, $installed_languages, true ) ) { ?>
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
							<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $flags as $flag ) { ?>
								<option
									value="<?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?>"<?php selected( $language['flag'], pathinfo( $flag, PATHINFO_FILENAME ) ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
							<?php } ?>
						</select>
						<?php if ( ( $language['flag'] ) ) { ?>
							<img src="<?php echo esc_url( WPM()->flag_dir() . $language['flag'] . '.png' ); ?>"
							     alt="<?php esc_attr_e( $language['name'] ); ?>">
						<?php } ?>
					</td>
					<td class="wpm-lang-delete">
						<?php if ( get_locale() === $key ) { ?>
							<?php esc_html_e( 'Current', 'wpm' ); ?>
						<?php } elseif ( wpm_get_default_locale() === $key ) { ?>
							<?php esc_html_e( 'Default', 'wpm' ); ?>
						<?php } elseif ( 'en_US' === $key ) { ?>
							<?php esc_html_e( 'Built-in', 'wpm' ); ?>
						<?php } elseif ( ! is_multisite() || ( is_main_site() ) ) { ?>
							<button type="button" class="button button-link delete-language"
							        data-locale="<?php echo $key; ?>"><?php esc_attr_e( 'Delete', 'wpm' ); ?></button>
						<?php } ?>
					</td>
				</tr>
				<?php $i ++;
			}// End foreach(). ?>
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
		<?php
	}

	/**
	 * Display translation setting
	 */
	public function translating_setting() {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php esc_html_e( 'Translating settings', 'wpm' ); ?></span>
			</legend>
			<label for="wpm_show_untranslated_strings">
				<input type="hidden" name="wpm_show_untranslated_strings" value="0">
				<input name="wpm_show_untranslated_strings" type="checkbox" id="wpm_show_untranslated_strings"
				       value="1"<?php checked( get_option( 'wpm_show_untranslated_strings' ) ); ?>>
				<?php esc_attr_e( 'Show untranslated strings in default language', 'wpm' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Display ininstall setting
	 */
	public function uninstalling_setting() {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php esc_html_e( 'Uninstalling', 'wpm' ); ?></span>
			</legend>
			<label for="wpm_uninstall_translations">
				<input type="hidden" name="wpm_uninstall_translations" value="0">
				<input name="wpm_uninstall_translations" type="checkbox" id="wpm_uninstall_translations"
				       value="1"<?php checked( get_option( 'wpm_uninstall_translations' ) ); ?>>
				<?php esc_attr_e( 'Delete translations when uninstalling plugin (some translations may not be deleted and you must delete them manually).', 'wpm' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Save WPM languages
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function save_languages( $value ) {

		check_admin_referer( 'wpm_save_settings', 'wpm_save_settings_nonce' );

		$option_name = 'wpm_languages';
		$languages   = array();

		if ( isset( $_POST[ $option_name ] ) ) {

			$type = '';

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
					'enable' => $_POST[ $option_name ][ $key ]['enable'] ? 1 : 0,
					'slug'   => wpm_clean( $item['slug'] ),
					'name'   => wpm_clean( $item['name'] ),
					'flag'   => wpm_clean( $item['flag'] ),
				);

				$translations        = wpm_get_translations();
				$installed_languages = wpm_get_installed_languages();

				foreach ( $installed_languages as $installed_language ) {
					unset( $translations[ $installed_language ] );
				}

				if ( in_array( $locale, $translations, true ) ) {
					if ( wp_can_install_language_pack() ) {
						wp_download_language_pack( $locale );
						$type = 'updated';
					}
				}
			}

			if ( 'updated' === $type ) {
				add_settings_error( $option_name, '', __( 'New language package installed', 'wpm' ), $type );
			}

			if ( 'error' === $type ) {
				add_settings_error( $option_name, '', __( 'Language slug is required', 'wpm' ), $type );

				return get_option( $option_name );

			}
		}// End if().

		return $languages;
	}
}
