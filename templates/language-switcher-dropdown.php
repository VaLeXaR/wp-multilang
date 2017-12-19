<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $type ); ?>">
	<li class="item-language-main item-language-<?php echo esc_attr( $lang ); ?>">
				<span>
					<?php if ( ( ( 'flag' === $show ) || ( 'both' === $show ) ) && ( $languages[ $lang ] ['flag'] ) ) { ?>
						<img src="<?php echo esc_url( wpm_get_flag_url( $languages[ $lang ]['flag'] ) ); ?>" alt="<?php esc_attr_e( $languages[ $lang ]['name'] ); ?>">
					<?php } ?>
					<?php if ( ( 'name' === $show ) || ( 'both' === $show ) ) { ?>
						<span><?php esc_html_e( $languages[ $lang ]['name'] ); ?></span>
					<?php } ?>
				</span>
		<ul class="language-dropdown">
			<?php foreach ( $languages as $code => $language ) { if ( wpm_get_language() == $code ) continue; ?>
				<li class="item-language-<?php echo esc_attr( $code ); ?><?php if ( $code === $lang ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( wpm_translate_current_url( $code ) ); ?>" data-lang="<?php echo esc_attr( $code ); ?>">
						<?php if ( ( ( 'flag' === $show ) || ( 'both' === $show ) ) && ( $language['flag'] ) ) { ?>
							<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ); ?>">
						<?php } ?>
						<?php if ( ( 'name' === $show ) || ( 'both' === $show ) ) { ?>
							<span><?php esc_html_e( $language['name'] ); ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	</li>
</ul>
