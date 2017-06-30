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
		'show' => 'both',
	);
	$args    = wp_parse_args( $args, $default );

	$languages = wpm_get_languages();

	if ( count( $languages ) <= 1 ) {
		return '';
	}

	$options     = wpm_get_options();
	$current_url = wpm_get_current_url();
	$locale      = get_locale();
	ob_start();
	if ( 'list' === $args['type'] ) { ?>
		<ul class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<li<?php if ( $key === $locale ) { ?> class="active"<?php } ?>>
					<a href="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>">
						<?php if ( ( ( 'flag' === $args['show'] ) || ( 'both' === $args['show'] ) ) && ( $options[ $key ]['flag'] ) ) { ?>
							<img src="<?php echo esc_url( WPM()->flag_dir() . $options[ $key ]['flag'] . '.png' ); ?>"
							    alt="<?php esc_attr_e( $options[ $key ]['name'] ); ?>">
						<?php } ?>
						<?php if ( ( 'name' === $args['show'] ) || ( 'both' === $args['show'] ) ) { ?>
							<span><?php esc_attr_e( $options[ $key ]['name'] ); ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
	<? }

	if ( 'dropdown' === $args['type'] ) { ?>
		<select class="wpm-language-switcher switcher-<?php esc_attr_e( $args['type'] ); ?>" onchange="location = this.value;"
		    title="<?php esc_html_e( __( 'Language Switcher', 'wpm' ) ); ?>">
			<?php foreach ( $languages as $key => $language ) { ?>
				<option
					value="<?php echo esc_url( wpm_translate_url( $current_url, $language ) ); ?>"<?php if ( $key === $locale ) { ?> selected="selected"<?php } ?>>
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
add_filter( 'gettext', 'wpm_translate_string' );
