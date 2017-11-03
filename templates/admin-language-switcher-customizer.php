<script id="tmpl-wpm-ls-customizer" type="text/template">
	<div id="wpm-language-switcher" class="wpm-language-switcher">
		<div class="lang-main">
			<?php if ( $languages[ $lang ]['flag'] ) { ?>
				<img src="<?php echo esc_url( wpm_get_flag_url( $languages[ $lang ]['flag'] ) ); ?>">
			<?php } else { ?>
				<?php esc_html_e( $languages[ $lang ]['name'] ); ?>
			<?php } ?>
		</div>
		<div class="lang-dropdown">
			<ul>
				<?php foreach ( $languages as $key => $language ) {
					if ( $key === $lang ) {
						continue;
					} ?>
					<li class="wpm-language-<?php esc_attr_e( $key ); ?>">
						<a href="<?php echo esc_url( add_query_arg( 'edit_lang', $key, $current_url ) ); ?>" data-lang="<?php esc_attr_e( $key ); ?>">
							<?php if ( $language['flag'] ) { ?>
								<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>"
								     alt="<?php esc_attr_e( $language['name'] ); ?>">
							<?php } else { ?>
								<?php esc_html_e( $language['name'] ); ?>
							<?php } ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</script>
