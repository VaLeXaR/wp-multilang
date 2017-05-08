<?php


/**
 * Language Select Code for non-Widget users
 * @args is a hash array of options, which accepts the following keys:
 *   ‘type’ – one of the values: ‘text’, ‘image’, ‘both’, ‘dropdown’ and ‘custom’, which match the choices on widget admin page.
 *   ‘format’ – needs to be provided if ‘type’ is ‘custom’. Read help text to this option on widget admin page.
 *   ‘id’ – id of widget, which is used as a distinctive string to create CSS entities.
 */
function qtn_language_switcher( $args = array(), $echo = true ) {
	global $qtn_config;
	$default = array(
		'type' => 'list',
		'flag' => true,
		'text' => true
	);
	$args = array_merge( $args, $default);

	$languages = $qtn_config->languages;
	$options = $qtn_config->options;
	$current_url = wp_get_referer();
	$locale = get_locale();
	ob_start();
	if ('list' == $args['type']) { ?>
		<ul class="qtn-language-switcher switcher-<?php echo $args['type']; ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li<?php if ( $key == $locale ) { ?> class="active"<?php } ?>>
					<a href="<?php echo qtn_localize_url( $current_url, $key ); ?>">
						<?php if ( $args['flag'] ) { ?>
							<img src="<?php echo QN()->plugin_url() . '/flags/' . $language . '.png'; ?>"
							     alt="<?php echo $options[ $key ]['name']; ?>">
						<?php } ?>
						<?php if ( $args['text'] ) { ?>
							<span><?php echo $options[ $key ]['name']; ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
		<? }

	if ('dropdown' == $args['type'] ) { ?>
		<select class="qtn-language-switcher switcher-<?php echo $args['type']; ?>" onchange="location = this.value;">
			<?php foreach ($languages as $key => $language) { ?>
				<option value="<?php echo qtn_localize_url( $current_url, $key ); ?>"<?php if ( $key == $locale ) { ?> selected="selected"<?php } ?>>
					<?php echo $options[ $key ]['name']; ?>
				</option>
			<?php } ?>
		</select>
	<?php }

	$content = ob_get_contents();
	ob_end_clean();

	if ( $echo ) {
		echo $content;
	} else {
		return $content;
	}
}

/*$language = array(
	'en_US' => array(
		'enable' => 1,
		'slug' => 'en',
		'name' => 'English'
	),
	'uk' => array(
		'enable' => 1,
		'slug' => 'uk',
		'name' => 'Українська'
	)
);

update_option( 'qtn_languages', $language, true);*/
