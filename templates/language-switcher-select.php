<select class="wpm-language-switcher switcher-<?php esc_attr_e( $type ); ?>" onchange="location = this.value;" title="<?php esc_html_e( __( 'Language Switcher', 'wp-multilang' ) ); ?>">
	<?php foreach ( $languages as $code => $language ) { ?>
		<option value="<?php echo esc_url( wpm_translate_url( $current_url, $code ) ); ?>"<?php if ( $code === $lang ) { ?> selected="selected"<?php } ?>data-lang="<?php esc_attr_e( $code ); ?>">
			<?php echo $language['name']; ?>
		</option>
	<?php } ?>
</select>
