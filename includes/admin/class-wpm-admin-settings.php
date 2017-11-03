<?php
/**
 * WPM Admin Settings Class
 *
 * @category Admin
 * @package  WPM/Includes/Admin
 */

namespace WPM\Includes\Admin;

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

		add_settings_section( 'wpm_setting_section', __( 'Multilingual Settings', 'wp-multilang' ),  '', 'general' );
		add_settings_field( 'wpm_site_language', __( 'Site Language' ), array( $this, 'site_language_setting' ), 'general', 'wpm_setting_section' );

		add_settings_field( 'wpm_languages', __( 'Languages', 'wp-multilang' ), array( $this, 'languages_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_languages', array(
			'sanitize_callback' => array( $this, 'save_languages' ),
		) );

		add_settings_field( 'wpm_show_untranslated_strings', __( 'Translating settings', 'wp-multilang' ), array( $this, 'translating_setting' ), 'general', 'wpm_setting_section' );
		register_setting( 'general', 'wpm_show_untranslated_strings' );

		if ( ! is_multisite() || ( is_main_site() ) ) {
			add_settings_field( 'wpm_uninstall_translations', __( 'Uninstalling', 'wp-multilang' ), array( $this, 'uninstalling_setting' ), 'general', 'wpm_setting_section' );
			register_setting( 'general', 'wpm_uninstall_translations' );
		}
	}

	/**
	 * Show site language from DB
	 */
	public function site_language_setting() {
		$languages      = wpm_get_options();
		$enable_locales = array();

		foreach ( $languages as $key => $language ) {
			if ( $language['enable'] ) {
				$enable_locales[] = $key;
			}
		}
		?>
		<select name="WPLANG" title="<?php esc_attr_e( 'Site Language' ); ?>">
			<?php foreach ( $enable_locales as $locale ) { ?>
				<option value="<?php echo $locale == 'en_US' ? '' : $locale; ?>"<?php selected( $locale, wpm_get_default_locale() ); ?>><?php esc_attr_e( $languages[ $locale ]['name'] ); ?></option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Display languages
	 */
	public function languages_setting() {
		$languages              = wpm_get_options();
		$installed_languages    = wpm_get_installed_languages();
		$available_translations = wpm_get_available_translations();
		$flags                  = wpm_get_flags();
		?>
		<table id="wpm-languages" class="wpm-languages widefat">
			<thead>
			<tr>
				<th class="wpm-lang-order"><?php esc_attr_e( 'Order', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-status"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-slug"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-name"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Date Format' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Time Format' ); ?></th>
				<th class="wpm-lang-flag"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-delete"><?php esc_attr_e( 'Delete' ); ?></th>
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
						<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="0">
						<input name="wpm_languages[<?php echo $i; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>"<?php if ( wpm_get_default_locale() === $key ) { ?> disabled="disabled"<?php } ?>>
						<?php if ( wpm_get_default_locale() === $key ) { ?>
							<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="1">
						<?php } ?>
					</td>
					<td class="wpm-lang-locale">
						<?php if ( isset( $available_translations[ $key ] ) ) { ?>
							<?php esc_attr_e( $key ); ?>
							<input type="hidden" name="wpm_languages[<?php echo $i; ?>][locale]" value="<?php esc_attr_e( $key ); ?>">
						<?php } else { ?>
							<input type="text" name="wpm_languages[<?php echo $i; ?>][locale]" value="<?php esc_attr_e( $key ); ?>" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" required>
						<?php } ?>
					</td>
					<td class="wpm-lang-slug"><input type="text" name="wpm_languages[<?php echo $i; ?>][slug]" value="<?php esc_attr_e( $language['slug'] ); ?>" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
					</td>
					<td class="wpm-lang-name">
						<input type="text" name="wpm_languages[<?php echo $i; ?>][name]" value="<?php esc_attr_e( $language['name'] ); ?>" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
					</td>
					<td class="wpm-lang-date">
						<input type="text" name="wpm_languages[<?php echo $i; ?>][date]" value="<?php esc_attr_e( $language['date'] ); ?>" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
					</td>
					<td class="wpm-lang-time">
						<input type="text" name="wpm_languages[<?php echo $i; ?>][time]" value="<?php esc_attr_e( $language['time'] ); ?>" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
					</td>
					<td class="wpm-lang-flag">
						<select class="wpm-flags" name="wpm_languages[<?php echo $i; ?>][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
							<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
							<?php foreach ( $flags as $flag ) { ?>
								<option value="<?php esc_attr_e( $flag ); ?>"<?php selected( $language['flag'], $flag ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
							<?php } ?>
						</select>
						<?php if ( ( $language['flag'] ) ) { ?>
							<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ); ?>">
						<?php } ?>
					</td>
					<td class="wpm-lang-delete">
						<?php if ( get_locale() === $key ) { ?>
							<?php esc_html_e( 'Current', 'wp-multilang' ); ?>
						<?php } elseif ( wpm_get_default_locale() === $key ) { ?>
							<?php esc_html_e( 'Default', 'wp-multilang' ); ?>
						<?php } elseif ( 'en_US' === $key ) { ?>
							<?php esc_html_e( 'Built-in', 'wp-multilang' ); ?>
						<?php } elseif ( ! is_multisite() || is_super_admin() ) { ?>
							<button type="button" class="button button-link delete-language" data-locale="<?php echo $key; ?>"><?php esc_attr_e( 'Delete' ); ?></button>
						<?php } ?>
					</td>
				</tr>
				<?php $i ++;
			}// End foreach(). ?>
			</tbody>
			<tfoot>
			<tr>
				<th class="wpm-lang-order"><?php esc_attr_e( 'Order', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-status"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-slug"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-name"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Date Format' ); ?></th>
				<th class="wpm-lang-locale"><?php esc_attr_e( 'Time Format' ); ?></th>
				<th class="wpm-lang-flag"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></th>
				<th class="wpm-lang-delete"><?php esc_attr_e( 'Delete' ); ?></th>
			</tr>
			</tfoot>
		</table>
		<?php

		if ( ! wp_can_install_language_pack() || ( is_multisite() && ! is_super_admin() ) ) {
			return;
		}

		array_unshift( $available_translations, array(
			'language'    => 1,
			'native_name' => __( 'Custom language', 'wp-multilang' ),
			'iso'         => array(),
		) );

		foreach ( $installed_languages as $installed_language ) {
			if ( isset( $available_translations[ $installed_language ] ) ) {
				unset( $available_translations[ $installed_language ] );
			}
		}
		?>
		<p class="submit">
			<select id="wpm-available-translations" title="<?php esc_attr_e( 'Available translations' ); ?>">
				<option value=""><?php esc_attr_e( '&mdash; Select language &mdash;', 'wp-multilang' ); ?></option>
				<?php foreach ( $available_translations as $translation ) { ?>
					<option value="<?php esc_attr_e( $translation['language'] ); ?>"><?php esc_attr_e( $translation['native_name'] ); ?></option>
				<?php } ?>
			</select>
			<input type="button" id="add_lang" class="button button-primary" value="<?php esc_attr_e( 'Add language', 'wp-multilang' ); ?>">
		</p>
		<script>
			var wpm_lang_count = <?php echo $i; ?>;
		</script>
		<script id="tmpl-wpm-add-lang" type="text/template">
			<tr>
				<td class="wpm-lang-order">{{ data.count }}</td>
				<td class="wpm-lang-status">
					<input type="hidden" name="wpm_languages[{{ data.count }}][enable]" value="0">
					<input name="wpm_languages[{{ data.count }}][enable]" type="checkbox" value="1" title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>" checked="checked">
				</td>
				<td class="wpm-lang-locale">
					<# if (data.language) { #>
						{{ data.language }}
						<input type="hidden" name="wpm_languages[{{ data.count }}][locale]" value="{{ data.language }}">
					<# } else { #>
						<input type="text" name="wpm_languages[{{ data.count }}][locale]" value="{{ data.language }}" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale', 'wp-multilang' ); ?>" required>
					<# } #>
				</td>
				<td class="wpm-lang-slug">
					<input type="text" name="wpm_languages[{{ data.count }}][slug]" value="{{ data.iso[Object.keys(data.iso)[0]] }}" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
				</td>
				<td class="wpm-lang-name">
					<input type="text" name="wpm_languages[{{ data.count }}][name]" value="{{ data.native_name }}" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
				</td>
				<td class="wpm-lang-date">
					<input type="text" name="wpm_languages[{{ data.count }}][date]" value="" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
				</td>
				<td class="wpm-lang-time">
					<input type="text" name="wpm_languages[{{ data.count }}][time]" value="" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
				</td>
				<td class="wpm-lang-flag">
					<select class="wpm-flags" name="wpm_languages[{{ data.count }}][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
						<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
						<?php foreach ( $flags as $flag ) { ?>
						<option value="<?php esc_attr_e( $flag ); ?>"><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
						<?php } ?>
					</select>
				</td>
				<td class="wpm-lang-delete">
					<button type="button" class="button button-link delete-language" data-locale="{{ data.language }}"><?php esc_attr_e( 'Delete' ); ?></button>
				</td>
			</tr>
		</script>
		<?php
	}

	/**
	 * Display translation setting
	 */
	public function translating_setting() {
		?>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php esc_html_e( 'Translating settings', 'wp-multilang' ); ?></span>
			</legend>
			<label for="wpm_show_untranslated_strings">
				<input type="hidden" name="wpm_show_untranslated_strings" value="0">
				<input name="wpm_show_untranslated_strings" type="checkbox" id="wpm_show_untranslated_strings" value="1"<?php checked( get_option( 'wpm_show_untranslated_strings' ) ); ?>>
				<?php esc_attr_e( 'Show untranslated strings in default language', 'wp-multilang' ); ?>
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
				<span><?php esc_html_e( 'Uninstalling', 'wp-multilang' ); ?></span>
			</legend>
			<label for="wpm_uninstall_translations">
				<input type="hidden" name="wpm_uninstall_translations" value="0">
				<input name="wpm_uninstall_translations" type="checkbox" id="wpm_uninstall_translations" value="1"<?php checked( get_option( 'wpm_uninstall_translations' ) ); ?>>
				<?php esc_attr_e( 'Delete translations when uninstalling plugin (some translations may not be deleted and you must delete them manually).', 'wp-multilang' ); ?>
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

		check_admin_referer( 'general-options' );

		$value       = wpm_clean( $value );
		$option_name = 'wpm_languages';
		$languages   = array();

		if ( wpm_get_post_data_by_key( $option_name ) ) {

			$type                = '';
			$translations        = wpm_get_available_translations();
			$installed_languages = wpm_get_installed_languages();

			foreach ( $installed_languages as $installed_language ) {
				if ( isset( $translations[ $installed_language ] ) ) {
					unset( $translations[ $installed_language ] );
				}
			}

			foreach ( $value as $item ) {

				if ( empty( $item['slug'] ) || empty( $item['locale'] ) ) {
					$type = 'error';
					break;
				}

				$locale = $item['locale'];

				$languages[ $locale ] = array(
					'enable' => $item['enable'] ? 1 : 0,
					'slug'   => sanitize_title( $item['slug'] ),
					'name'   => $item['name'],
					'date'   => $item['date'],
					'time'   => $item['time'],
					'flag'   => $item['flag'],
				);

				if ( isset( $translations[ $locale ] ) && wp_can_install_language_pack() && ( ! is_multisite() || is_super_admin() ) ) {
					wp_download_language_pack( $locale );
					$type = 'updated';
				}
			}

			if ( 'updated' === $type ) {
				add_settings_error( $option_name, '', __( 'New language package installed', 'wp-multilang' ), $type );
			}

			if ( 'error' === $type ) {
				add_settings_error( $option_name, '', __( 'Language slug and locale is required', 'wp-multilang' ), $type );

				return get_option( $option_name );

			}
		}// End if().

		return $languages;
	}
}
