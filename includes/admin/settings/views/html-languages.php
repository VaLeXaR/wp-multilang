<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
