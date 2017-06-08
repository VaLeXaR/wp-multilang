<?php
/**
 * WPM Template functions
 *
 * Functions for using in template.
 *
 * @author        VaLeXaR
 * @category      Core
 * @package       WPM/Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display language switcher in templates
 *
 * @param array $args
 * @param bool  $echo
 *
 * @return string
 */
function wpm_language_switcher( $args = array(), $echo = true ) {
	$default = array(
		'type' => 'list',
		'show' => 'both'
	);
	$args    = array_merge( $default, $args );

	$languages = wpm_get_languages();

	if ( count( $languages ) <= 1 ) {
		return '';
	}

	$options     = wpm_get_options();
	$current_url = wpm_get_current_url();
	$locale      = get_locale();
	ob_start();
	if ( 'list' == $args['type'] ) { ?>
		<ul class="wpm-language-switcher switcher-<?php echo $args['type']; ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li<?php if ( $key == $locale ) { ?> class="active"<?php } ?>>
					<a href="<?php echo wpm_translate_url( $current_url, $language ); ?>">
						<?php if ( ( ( $args['show'] == 'flag' ) || ( $args['show'] == 'both' ) ) && ( $options[ $key ]['flag'] ) ) { ?>
							<img src="<?php echo WPM()->flag_dir() . $options[ $key ]['flag'] . '.png'; ?>"
							     alt="<?php echo $options[ $key ]['name']; ?>">
						<?php } ?>
						<?php if ( ( $args['show'] == 'name' ) || ( $args['show'] == 'both' ) ) { ?>
							<span><?php echo $options[ $key ]['name']; ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<? }

	if ( 'dropdown' == $args['type'] ) { ?>
		<select class="wpm-language-switcher switcher-<?php echo $args['type']; ?>" onchange="location = this.value;"
		        title="<?php esc_html_e( __( 'Language Switcher', 'wpm' ) ); ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<option
					value="<?php echo wpm_translate_url( $current_url, $language ); ?>"<?php if ( $key == $locale ) { ?> selected="selected"<?php } ?>>
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


add_filter( 'localization', 'wpm_translate_string' );
