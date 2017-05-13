<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Language Select Code for non-Widget users
 * @args is a hash array of options, which accepts the following keys:
 *   ‘type’ – one of the values: ‘text’, ‘image’, ‘both’, ‘dropdown’ and ‘custom’, which match the choices on widget admin page.
 *   ‘format’ – needs to be provided if ‘type’ is ‘custom’. Read help text to this option on widget admin page.
 *   ‘id’ – id of widget, which is used as a distinctive string to create CSS entities.
 */
function wpm_language_switcher( $args = array(), $echo = true ) {
	global $wp;
	$default = array(
		'type' => 'list',
		'flag' => true,
		'text' => true
	);
	$args = array_merge( $args, $default);

	$languages = wpm_get_languages();

	if ( count( $languages ) <= 1 ) {
		return '';
	}

	$options = wpm_get_options();
	$current_url = home_url( $wp->request );
	$locale = get_locale();
	ob_start();
	if ('list' == $args['type']) { ?>
		<ul class="wpm-language-switcher switcher-<?php echo $args['type']; ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li<?php if ( $key == $locale ) { ?> class="active"<?php } ?>>
					<a href="<?php echo wpm_translate_url( $current_url, $key ); ?>">
						<?php if ( $args['flag'] ) { ?>
							<img src="<?php echo WPM()->flag_dir() . $options[ $key ]['flag'] . '.png'; ?>"
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
		<select class="wpm-language-switcher switcher-<?php echo $args['type']; ?>" onchange="location = this.value;" title="<?php esc_html_e( __('Language Switcher', 'wpm')); ?>">
			<?php foreach ($languages as $key => $language) { ?>
				<option value="<?php echo wpm_translate_url( $current_url, $key ); ?>"<?php if ( $key == $locale ) { ?> selected="selected"<?php } ?>>
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
