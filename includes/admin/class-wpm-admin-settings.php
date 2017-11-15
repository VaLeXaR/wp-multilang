<?php
/**
 * WP Multilang Admin Settings Class
 *
 * @category Admin
 * @package  WPM/Admin
 */

namespace WPM\Includes\Admin;
use WPM\Includes\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPM_Admin_Settings Class.
 */
class WPM_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors   = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			$settings[] = new Settings\WPM_Settings_General();
			$settings[] = new Settings\WPM_Settings_Languages();

			self::$settings = apply_filters( 'wpm_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpm-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'wp-multilang' ) );
		}

		// Trigger actions
		do_action( 'wpm_settings_save_' . $current_tab );
		do_action( 'wpm_update_options_' . $current_tab );
		do_action( 'wpm_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'wp-multilang' ) );
		self::check_download_folder_protection();

		// Clear any unwanted data and flush rules
//		delete_transient( 'wpm_cache_excluded_uris' );
//		WC()->query->init_query_vars();
//		WC()->query->add_endpoints();
//		wp_schedule_single_event( time(), 'wpm_flush_rewrite_rules' );

		do_action( 'wpm_settings_saved' );
	}

	/**
	 * Add a message.
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error.
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors.
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error inline"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated inline"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main WP Multilang settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab;

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		do_action( 'wpm_settings_start' );

		wp_enqueue_script( 'wpm_settings', WC()->plugin_url() . '/assets/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'selectWoo' ), WC()->version, true );

		wp_localize_script( 'wpm_settings', 'wpm_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'wp-multilang' ),
		) );

		// Get tabs for the settings page
		$tabs = apply_filters( 'wpm_settings_tabs_array', array() );

		include( dirname( __FILE__ ) . '/views/html-admin-settings.php' );
	}

	/**
	 * Get a setting from the settings API.
	 *
	 * @param string $option_name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function get_option( $option_name, $default = '' ) {
		// Array value
		if ( strstr( $option_name, '[' ) ) {

			parse_str( $option_name, $option_array );

			// Option name is first key
			$option_name = current( array_keys( $option_array ) );

			// Get value
			$option_values = get_option( $option_name, '' );

			$key = key( $option_array[ $option_name ] );

			if ( isset( $option_values[ $key ] ) ) {
				$option_value = $option_values[ $key ];
			} else {
				$option_value = null;
			}

		// Single value
		} else {
			$option_value = get_option( $option_name, null );
		}

		if ( is_array( $option_value ) ) {
			$option_value = array_map( 'stripslashes', $option_value );
		} elseif ( ! is_null( $option_value ) ) {
			$option_value = stripslashes( $option_value );
		}

		return ( null === $option_value ) ? $default : $option_value;
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the WP Multilang options array and outputs each field.
	 *
	 * @param array[] $options Opens array to output
	 */
	public static function output_fields( $options ) {
		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['default'] ) ) {
				$value['default'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling
			$field_description = self::get_field_description( $value );
			extract( $field_description );

			// Switch based on type
			switch ( $value['type'] ) {

				// Section Titles
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
					}
					if ( ! empty( $value['desc'] ) ) {
						echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
					}
					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wpm_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				// Section Ends
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wpm_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wpm_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'
				case 'text':
				case 'email':
				case 'number':
				case 'password' :
					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Textarea
				case 'textarea':

					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php echo $description; ?>

							<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value );  ?></textarea>
						</td>
					</tr><?php
					break;

				// Select boxes
				case 'select' :
				case 'multiselect' :

					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
								>
								<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php

											if ( is_array( $option_value ) ) {
												selected( in_array( $key, $option_value ), true );
											} else {
												selected( $option_value, $key );
											}

										?>><?php echo $val ?></option>
										<?php
									}
								?>
							</select> <?php echo $description; ?>
						</td>
					</tr><?php
					break;

				// Radio inputs
				case 'radio' :

					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
									foreach ( $value['options'] as $key => $val ) {
										?>
										<li>
											<label><input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												value="<?php echo $key; ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
												<?php echo implode( ' ', $custom_attributes ); ?>
												<?php checked( $key, $option_value ); ?>
												/> <?php echo $val ?></label>
										</li>
										<?php
									}
								?>
								</ul>
							</fieldset>
						</td>
					</tr><?php
					break;

				// Checkbox input
				case 'checkbox' :

					$option_value    = self::get_option( $value['id'], $value['default'] );
					$visibility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
						$visibility_class[] = 'hidden_option';
					}
					if ( 'option' == $value['hide_if_checked'] ) {
						$visibility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' == $value['show_if_checked'] ) {
						$visibility_class[] = 'show_options_if_checked';
					}

					if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
						?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
								<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
					} else {
						?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
						<?php
					}

					if ( ! empty( $value['title'] ) ) {
						?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ) ?></span></legend>
						<?php
					}

					?>
						<label for="<?php echo $value['id'] ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
								value="1"
								<?php checked( $option_value, 'yes' ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/> <?php echo $description ?>
						</label> <?php echo $tooltip_html; ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
									?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
					break;

				// Country multiselects
				case 'languages' :
					$languages = wpm_get_lang_option();
					$flags     = wpm_get_flags();
					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
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
					</tr><?php
					break;

				// Default: run an action
				default:
					do_action( 'wpm_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

	/**
	 * Helper function to get the formatted description and tip HTML for a
	 * given form field. Plugins can call this when implementing their own custom
	 * settings types.
	 *
	 * @param  array $value The form field value array
	 * @return array The description and tip as a 2 element array
	 */
	public static function get_field_description( $value ) {
		$description  = '';
		$tooltip_html = '';

		if ( true === $value['desc_tip'] ) {
			$tooltip_html = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description  = $value['desc'];
			$tooltip_html = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description  = $value['desc'];
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$tooltip_html = '<p class="description">' . $tooltip_html . '</p>';
		} elseif ( $tooltip_html ) {
			$tooltip_html = wpm_help_tip( $tooltip_html );
		}

		return array(
			'description'  => $description,
			'tooltip_html' => $tooltip_html,
		);
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the WP Multilang options array and outputs each field.
	 *
	 * @param array $options Options array to output
	 * @param array $data Optional. Data to use for saving. Defaults to $_POST.
	 * @return bool
	 */
	public static function save_fields( $options, $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_POST;
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox' :
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea' :
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect' :
					$value = array_filter( array_map( 'wpm_clean', (array) $raw_value ) );
					break;
				case 'languages' :
					$value       = array_filter( array_map( 'wpm_clean', (array) $raw_value ) );
					$languages   = array();
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
							return get_option( $option_name );
						}

					return apply_filters( 'wpm_save_languages', $languages, $value );
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_keys( $option['options'] );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values ) ? $raw_value : $default;
					break;
				default :
					$value = wpm_clean( $raw_value );
					break;
			}

			/**
			 * Sanitize the value of an option.
			 * @since 2.4.0
			 */
			$value = apply_filters( 'wpm_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 * @since 2.4.0
			 */
			$value = apply_filters( "wpm_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}
		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		return true;
	}

	/**
	 * Checks which method we're using to serve downloads.
	 *
	 * If using force or x-sendfile, this ensures the .htaccess is in place.
	 */
	public static function check_download_folder_protection() {
		$upload_dir      = wp_upload_dir();
		$downloads_url   = $upload_dir['basedir'] . '/wpm_uploads';
		$download_method = get_option( 'wpm_file_download_method' );

		if ( 'redirect' == $download_method ) {

			// Redirect method - don't protect
			if ( file_exists( $downloads_url . '/.htaccess' ) ) {
				unlink( $downloads_url . '/.htaccess' );
			}
		} else {

			// Force method - protect, add rules to the htaccess file
			if ( ! file_exists( $downloads_url . '/.htaccess' ) ) {
				if ( $file_handle = @fopen( $downloads_url . '/.htaccess', 'w' ) ) {
					fwrite( $file_handle, 'deny from all' );
					fclose( $file_handle );
				}
			}
		}
	}
}
