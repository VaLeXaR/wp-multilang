<select class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>" onchange="location = this.value;" title="<?php esc_html_e( __( 'Language Switcher', 'wp-multilang' ) ); ?>">
	<?php foreach ( $languages as $key => $language ) { ?>
		<option value="<?php echo esc_url( wpm_translate_url( $current_url, $key ) ); ?>"<?php if ( $key === $lang ) { ?> selected="selected"<?php } ?> data-lang="<?php esc_attr_e( $key ); ?>">
			<?php echo $language['name']; ?>
		</option>
	<?php } ?>
</select>
