<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>">
	<li class="item-language-main item-language-<?php esc_attr_e( $options[ $locales[ $lang ] ]['slug'] ); ?>">
				<span>
					<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $options[ $locales[ $lang ] ]['flag'] ) ) { ?>
						<img src="<?php echo esc_url( wpm_get_flag_url( $options[ $locales[ $lang ] ]['flag'] ) ); ?>" alt="<?php esc_attr_e( $options[ $locales[ $lang ] ]['name'] ); ?>">
					<?php } ?>
					<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
						<span><?php esc_attr_e( $options[ $locales[ $lang ] ]['name'] ); ?></span>
					<?php } ?>
				</span>
		<ul class="language-dropdown">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li class="item-language-<?php esc_attr_e( $options[ $key ]['slug'] ); ?><?php if ( $key === $locale ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>" data-lang="<?php esc_attr_e( $language ); ?>">
						<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $options[ $key ]['flag'] ) ) { ?>
							<img src="<?php echo esc_url( wpm_get_flag_url( $options[ $key ]['flag'] ) ); ?>" alt="<?php esc_attr_e( $options[ $key ]['name'] ); ?>">
						<?php } ?>
						<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
							<span><?php esc_attr_e( $options[ $key ]['name'] ); ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	</li>
</ul>
