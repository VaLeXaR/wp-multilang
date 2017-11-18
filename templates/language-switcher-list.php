<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $type ); ?>">
	<?php foreach ( $languages as $code => $language ) { ?>
		<li class="item-language-<?php esc_attr_e( $code ); ?><?php if ( $code === $lang ) { ?> active<?php } ?>">
			<a href="<?php echo esc_url( wpm_translate_url( $current_url, $code ) ); ?>" data-lang="<?php esc_attr_e( $code ); ?>">
				<?php if ( ( ( 'flag' === $show ) || ( 'both' === $show ) ) && ( $language['flag'] ) ) { ?>
					<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ); ?>">
				<?php } ?>
				<?php if ( ( 'name' === $show ) || ( 'both' === $show ) ) { ?>
					<span><?php esc_attr_e( $language['name'] ); ?></span>
				<?php } ?>
			</a>
		</li>
	<?php } ?>
</ul>
