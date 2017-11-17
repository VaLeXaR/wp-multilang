<?php
/**
 * Admin View: Settings
 *
 * @package wpm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_exists        = isset( $tabs[ $current_tab ] ) || has_action( 'wpm_sections_' . $current_tab ) || has_action( 'wpm_settings_' . $current_tab ) || has_action( 'wpm_settings_tabs_' . $current_tab );
$current_tab_label = isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : '';

if ( ! $tab_exists ) {
	wp_safe_redirect( admin_url( 'options-general.php?page=wpm-settings' ) );
	exit;
}
?>
<div class="wrap wpm">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<form method="<?php echo esc_attr( apply_filters( 'wpm_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper wpm-nav-tab-wrapper">
			<?php

			foreach ( $tabs as $slug => $label ) {
				echo '<a href="' . esc_html( admin_url( 'options-general.php?page=wpm-settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
			}

			do_action( 'wpm_settings_tabs' );

			?>
		</nav>
		<h2 class="screen-reader-text"><?php echo esc_html( $current_tab_label ); ?></h2>
		<?php
			do_action( 'wpm_sections_' . $current_tab );

			self::show_messages();

			do_action( 'wpm_settings_' . $current_tab );
		?>
		<p class="submit">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<?php submit_button(); ?>
			<?php endif; ?>
			<?php wp_nonce_field( 'wpm-settings' ); ?>
		</p>
	</form>
</div>
