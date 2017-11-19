<ul class="wpm-language-switcher switcher-<?php echo esc_attr( $type ); ?>">
	<?php foreach ( $languages as $code => $language ) { ?>
		<li class="item-language-<?php echo esc_attr( $code ); ?><?php if ( $code === $lang ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( wpm_translate_url( $current_url, $code ) ); ?>" data-lang="<?php echo esc_attr( $code ); ?>">
				<?php if ( ( ( 'flag' === $show ) || ( 'both' === $show ) ) && ( $language['flag'] ) ) { ?>
					<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php echo esc_attr( $language['name'] ); ?>">
				<?php } ?>
				<?php if ( ( 'name' === $show ) || ( 'both' === $show ) ) { ?>
					<span><?php echo esc_html( $language['name'] ); ?></span>
				<?php } ?>
			</a>
		</li>
	<?php } ?>
</ul>
