<?php
/**
 * Uninstall script for n8n Webhook for Elementor by Pixelfarben
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove options
delete_option( 'pf_n8n_settings' );
delete_option( 'pf_n8n_logs' );


