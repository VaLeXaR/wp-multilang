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
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp">
				<div id="poststuff">
					<div id="wpm-languages" class="wpm-languages meta-box-sortables">
						<?php $i = 1;
						foreach ( $languages as $key => $language ) { ?>
							<?php if ( ! is_string( $key ) ) {
								continue;
							} ?>
							<div class="postbox closed">
								<button type="button" class="handlediv" aria-expanded="true">
									<span class="toggle-indicator" aria-hidden="true"></span>
								</button>
								<div class="language-status">
									<?php if ( wpm_get_user_language() === $key ) { ?>
										<?php esc_html_e( 'Current', 'wp-multilang' ); ?>
									<?php } elseif ( wpm_get_default_language() === $key ) { ?>
										<?php esc_html_e( 'Default', 'wp-multilang' ); ?>
									<?php } ?>
								</div>
								<h2 class="hndle ui-sortable-handle">
									<span class="language-order"><?php esc_attr_e( $i ); ?></span>
									<span><?php esc_attr_e( $language['name'] ); ?></span>
								</h2>
								<div class="inside">
									<table class="widefat">
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></td>
											<td>
												<input type="text" name="wpm_languages[<?php echo $i; ?>][name]" value="<?php esc_attr_e( $language['name'] ); ?>" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></td>
											<td>
												<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="0">
												<input name="wpm_languages[<?php echo $i; ?>][enable]" type="checkbox" value="1"<?php checked( $language['enable'] ); ?> title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>"<?php if ( wpm_get_default_language() === $key ) { ?> disabled="disabled"<?php } ?>>
												<?php if ( wpm_get_default_language() === $key ) { ?>
													<input type="hidden" name="wpm_languages[<?php esc_attr_e( $i ) ; ?>][enable]" value="1">
												<?php } ?>
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></td>
											<td>
												<input type="text" name="wpm_languages[<?php echo $i; ?>][slug]" value="<?php esc_attr_e( $key ); ?>" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></td>
											<td>
												<input type="text" name="wpm_languages[<?php echo $i; ?>][locale]" value="<?php esc_attr_e( $language['locale'] ); ?>" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" required>
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Translation', 'wp-multilang' ); ?></td>
											<td>
												<?php
												wp_dropdown_languages( array(
													'name'                        => 'wpm_languages[' . $i . '][translation]',
													'id'                          => 'wpm_languages[' . $i . '][translation]',
													'selected'                    => $language['translation'],
													'languages'                   => get_available_languages(),
													'show_available_translations' => current_user_can( 'install_languages' ),
												) );
												?>
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Date Format' ); ?></td>
											<td>
												<input type="text" name="wpm_languages[<?php echo $i; ?>][date]" value="<?php esc_attr_e( $language['date'] ); ?>" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Time Format' ); ?></td>
											<td>
												<input type="text" name="wpm_languages[<?php echo $i; ?>][time]" value="<?php esc_attr_e( $language['time'] ); ?>" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
											</td>
										</tr>
										<tr>
											<td class="row-title"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></td>
											<td>
												<select class="wpm-flags" name="wpm_languages[<?php echo $i; ?>][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
													<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
													<?php foreach ( $flags as $flag ) { ?>
														<option value="<?php esc_attr_e( $flag ); ?>" data-flag="<?php echo esc_url( wpm_get_flag_url( $flag ) ); ?>" <?php selected( $language['flag'], $flag ); ?>><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
													<?php } ?>
												</select>
											</td>
										</tr>
										<?php do_action( 'wpm_language_settings', $key, $i ); ?>
										<?php if ( ( wpm_get_user_language() !== $key ) && ( wpm_get_default_language() !== $key ) ) { ?>
											<tr>
												<td class="row-title"></td>
												<td>
													<button type="button" class="button button-link delete-language" data-language="<?php echo $key; ?>"><?php esc_attr_e( 'Delete' ); ?></button>
											</tr>
										<?php } ?>
									</table>
								</div>
							</div>
							<?php $i ++;
						}// End foreach(). ?>
					</div>
				</div>
				<script>
					var wpm_lang_count = <?php echo $i; ?>;
				</script>
				<script id="tmpl-wpm-add-lang" type="text/template">
					<div class="postbox">
						<button type="button" class="handlediv" aria-expanded="true">
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
						<h2 class="hndle ui-sortable-handle">
							<span class="language-order">{{ data.count }}</span>
							<span></span>
						</h2>
						<div class="inside">
							<table class="widefat">
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Name', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[{{ data.count }}][name]" value="" title="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Name', 'wp-multilang' ); ?>">
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Enable', 'wp-multilang' ); ?></td>
									<td>
										<input type="hidden" name="wpm_languages[{{ data.count }}][enable]" value="0">
										<input name="wpm_languages[{{ data.count }}][enable]" type="checkbox" value="1" title="<?php esc_attr_e( 'Enable', 'wp-multilang' ); ?>" checked="checked">
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[{{ data.count }}][slug]" value="" title="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Slug *', 'wp-multilang' ); ?>" required>
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[{{ data.count }}][locale]" value="" title="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" placeholder="<?php esc_attr_e( 'Locale *', 'wp-multilang' ); ?>" required>
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Translation', 'wp-multilang' ); ?></td>
									<td>
										<?php
										wp_dropdown_languages( array(
											'name'                        => 'wpm_languages[{{ data.count }}][translation]',
											'id'                          => 'wpm_languages[{{ data.count }}][translation]',
											'languages'                   => get_available_languages(),
											'show_available_translations' => current_user_can( 'install_languages' ),
										) );
										?>
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Date Format' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[{{ data.count }}][date]" value="" title="<?php esc_attr_e( 'Date Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'date_format' ) ); ?>">
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Time Format' ); ?></td>
									<td>
										<input type="text" name="wpm_languages[{{ data.count }}][time]" value="" title="<?php esc_attr_e( 'Time Format' ); ?>" placeholder="<?php esc_attr_e( get_option( 'time_format' ) ); ?>">
									</td>
								</tr>
								<tr>
									<td class="row-title"><?php esc_attr_e( 'Flag', 'wp-multilang' ); ?></td>
									<td>
										<select class="wpm-flags" name="wpm_languages[{{ data.count }}][flag]" title="<?php esc_attr_e( 'Flag', 'wp-multilang' ); ?>">
											<option value=""><?php esc_attr_e( '&mdash; Select &mdash;' ); ?></option>
											<?php foreach ( $flags as $flag ) { ?>
												<option value="<?php esc_attr_e( $flag ); ?>" data-flag="<?php echo esc_url( wpm_get_flag_url( $flag ) ); ?>"><?php esc_attr_e( pathinfo( $flag, PATHINFO_FILENAME ) ); ?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
								<?php do_action( 'wpm_language_settings', '', '{{ data.count }}' ); ?>
								<tr>
									<td class="row-title"></td>
									<td>
										<button type="button" class="button button-link delete-language" data-language=""><?php esc_attr_e( 'Delete' ); ?></button>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</script>
				<p class="submit">
				<input type="button" id="add_lang" class="button button-primary" value="<?php esc_attr_e( 'Add language', 'wp-multilang' ); ?>">
			</p>
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
		$options                 = wpm_get_lang_option();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp">
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
						?>
						<option value="<?php esc_attr_e( $localization ); ?>"<?php if ( ( 'en_US' == $localization ) || $used ) { ?> disabled="disabled" <?php } ?>><?php esc_attr_e( $available_translations[ $localization ]['native_name'] ); ?></option>
					<?php } ?>
				</select>
				<input type="button" id="delete_localization" class="button" value="<?php esc_attr_e( 'Delete localization', 'wp-multilang' ); ?>">
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

				$slug = sanitize_title( $item['slug'] );

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
