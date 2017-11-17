<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="wpm-message notice notice-success">
	<a class="wpm-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpm-hide-notice', 'update', remove_query_arg( 'do_update_wpm' ) ), 'wpm_hide_notices_nonce', '_wpm_notice_nonce' ) ); ?>"><span class="screen-reader-text"><?php _e( 'Dismiss', 'wp-multilang' ); ?></a>

	<p><?php _e( 'WP Multilang data update complete. Thank you for updating to the latest version!', 'wp-multilang' ); ?></p>
</div>
