<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $type ); ?>">
	<?php foreach ( $languages as $code => $language ) { ?>
		<li class="item-language-<?php echo esc_attr( $code ); ?><?php if ( $code === $lang ) { ?> active<?php } ?>">
			<?php if ( wpm_get_language() == $code ) { ?>
				<span data-lang="<?php echo esc_attr( $code ); ?>">
			<?php } else { ?>
				<a href="<?php echo esc_url( wpm_translate_current_url( $code ) ); ?>" data-lang="<?php echo esc_attr( $code ); ?>">
			<?php } ?>
				<?php if ( ( ( 'flag' === $show ) || ( 'both' === $show ) ) && ( $language['flag'] ) ) { ?>
					<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php echo esc_attr( $language['name'] ); ?>">
				<?php } ?>
				<?php if ( ( 'name' === $show ) || ( 'both' === $show ) ) { ?>
					<span><?php esc_html_e( $language['name'] ); ?></span>
				<?php } ?>
			<?php if ( wpm_get_language() == $code ) { ?>
				</span>
			<?php } else { ?>
				</a>
			<?php } ?>
		</li>
	<?php } ?>
</ul>
