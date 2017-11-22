<select class="wpm-language-switcher switcher-<?php esc_attr_e( $type ); ?>" onchange="location = this.value;" title="<?php esc_html_e( __( 'Language Switcher', 'wp-multilang' ) ); ?>">
	<?php foreach ( $languages as $code => $language ) { ?>
		<option value="<?php echo esc_url( wpm_translate_current_url( $code ) ); ?>"<?php if ( $code === $lang ) { ?> selected="selected"<?php } ?>data-lang="<?php echo esc_attr( $code ); ?>">
			<?php esc_html_e( $language['name'] ); ?>
		</option>
	<?php } ?>
</select>
