<select class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>" onchange="location = this.value;" title="<?php esc_html_e( __( 'Language Switcher', 'wp-multilang' ) ); ?>">
	<?php foreach ( $languages as $key => $language ) { ?>
		<option value="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>"<?php if ( $key === $locale ) { ?> selected="selected"<?php } ?> data-lang="<?php esc_attr_e( $language ); ?>">
			<?php echo $options[ $key ]['name']; ?>
		</option>
	<?php } ?>
</select>
