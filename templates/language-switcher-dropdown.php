<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>">
	<li class="item-language-main item-language-<?php esc_attr_e( $lang ); ?>">
				<span>
					<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $languages[ $lang ] ['flag'] ) ) { ?>
						<img src="<?php echo esc_url( wpm_get_flag_url( $languages[ $lang ]['flag'] ) ); ?>" alt="<?php esc_attr_e( $languages[ $lang ]['name'] ); ?>">
					<?php } ?>
					<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
						<span><?php esc_attr_e( $languages[ $lang ]['name'] ); ?></span>
					<?php } ?>
				</span>
		<ul class="language-dropdown">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li class="item-language-<?php esc_attr_e( $language['slug'] ); ?><?php if ( $key === $lang ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( wpm_translate_url( $current_url, $key ) ); ?>" data-lang="<?php esc_attr_e( $key ); ?>">
						<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $language['flag'] ) ) { ?>
							<img src="<?php echo esc_url( wpm_get_flag_url( $language['flag'] ) ); ?>" alt="<?php esc_attr_e( $language['name'] ); ?>">
						<?php } ?>
						<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
							<span><?php esc_attr_e( $language['name'] ); ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	</li>
</ul>
