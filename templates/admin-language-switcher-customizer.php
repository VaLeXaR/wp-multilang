<script id="tmpl-wpm-ls-customizer" type="text/template">
	<div id="wpm-language-switcher" class="wpm-language-switcher">
		<div class="lang-main">
			<?php if ( $options[ $locales[ $lang ] ]['flag'] ) { ?>
				<img src="<?php echo esc_url( WPM()->flags_dir() . $options[ $locales[ $lang ] ]['flag'] . '.png' ); ?>">
			<?php } else { ?>
				<?php esc_html_e( $options[ $locales[ $lang ] ]['name'] ); ?>
			<?php } ?>
		</div>
		<div class="lang-dropdown">
			<ul>
				<?php foreach ( $languages as $key => $language ) {
					if ( $language === $lang ) {
						continue;
					} ?>
					<li class="wpm-language-<?php esc_attr_e( $language ); ?>">
						<a href="<?php echo esc_url( add_query_arg( 'edit_lang', $language, $current_url ) ); ?>" data-lang="<?php esc_attr_e( $language ); ?>">
							<?php if ( $options[ $key ]['flag'] ) { ?>
								<img src="<?php echo esc_url( WPM()->flags_dir() . $options[ $key ]['flag'] . '.png' ); ?>"
								     alt="<?php esc_attr_e( $options[ $key ]['name'] ); ?>">
							<?php } else { ?>
								<?php esc_html_e( $options[ $key ]['name'] ); ?>
							<?php } ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</script>
