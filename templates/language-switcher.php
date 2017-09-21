<?php
$languages = wpm_get_languages();
$lang      = wpm_get_language();
$options   = wpm_get_options();
?>
<script id="tmpl-wpm-ls" type="text/template">
	<h3 id="wpm-language-switcher" class="nav-tab-wrapper language-switcher">
		<?php foreach ( $languages as $key => $language ) { ?>
			<a class="nav-tab<?php if ( $lang === $language ) { ?> nav-tab-active<?php } ?>" href="<?php echo esc_url( add_query_arg( 'edit_lang', $language, wpm_get_current_url() ) ); ?>">
				<?php if ( $options[ $key ]['flag'] ) { ?>
				<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $key ]['flag'] . '.png' ); ?>" alt="<?php esc_attr_e( $options[ $key ]['name'] ) ; ?>">
				<?php } ?>
				<span><?php esc_attr_e( $options[ $key ]['name'] ); ?></span>
			</a>
		<?php } ?>
		</h3>
</script>
