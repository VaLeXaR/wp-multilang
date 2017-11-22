<script id="tmpl-wpm-ls" type="text/template">
	<h3 id="wpm-language-switcher" class="nav-tab-wrapper language-switcher">
		<?php foreach ( $languages as $code => $language ) { ?>
			<a class="nav-tab<?php if ( $code === $lang ) { ?> nav-tab-active<?php } ?>" href="<?php echo esc_url( add_query_arg( 'edit_lang', $code, wpm_get_current_url() ) ); ?>" data-lang="<?php echo esc_attr( $code ); ?>">
				<?php if ( $language['flag'] ) { ?>
				<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ) ; ?>">
				<?php } ?>
				<span><?php esc_html_e( $language['name'] ); ?></span>
			</a>
		<?php } ?>
		</h3>
</script>
