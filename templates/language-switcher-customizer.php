<?php
$languages = wpm_get_languages();
$locales   = array_flip( $languages );
$lang      = wpm_get_language();
$options   = wpm_get_options();
?>
<script id="tmpl-wpm-ls" type="text/template">
	<div id="wpm-language-switcher" class="wpm-language-switcher customize-controls-close">
		<div class="lang-main">
			<?php if ( $options[ $locales[ $lang ] ]['flag'] ) { ?>
				<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $locales[ $lang ] ]['flag'] . '.png' ); ?>">
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
						<a href="<?php echo esc_url( add_query_arg( 'edit_lang', $language, wpm_get_current_url() ) ); ?>">
							<?php if ( $options[ $key ]['flag'] ) { ?>
								<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $key ]['flag'] . '.png' ); ?>"
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
