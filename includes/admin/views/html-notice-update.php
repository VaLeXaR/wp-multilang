<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="wpm-message notice notice-success">
	<p><strong><?php _e( 'WP Multilang data update', 'wp-multilang' ); ?></strong> &#8211; <?php _e( 'We need to update your site database to the latest version.', 'wp-multilang' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_wpm', 'true', admin_url( 'options-general.php?page=wpm-settings' ) ) ); ?>" class="wpm-update-now button-primary"><?php _e( 'Run the updater', 'wp-multilang' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.wpm-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wp-multilang' ) ); ?>' ); // jshint ignore:line
	});
</script>
