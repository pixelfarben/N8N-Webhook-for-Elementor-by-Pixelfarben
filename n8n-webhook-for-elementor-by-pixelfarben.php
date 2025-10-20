<?php
/*
Plugin Name: Pixelfarben — n8n Webhook for Elementor
Plugin URI: https://pixelfarben.de
Description: Adds an "n8n Trigger (Pixelfarben)" action for Elementor Forms to send submissions to an n8n webhook with optional Secret Key signature, logging, retries, and privacy controls.
Version: 1.0.0
Author: Pixelfarben
Text Domain: n8n-webhook-for-elementor-by-pixelfarben
Domain Path: /languages
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PF_N8N_VERSION', '1.0.0' );
define( 'PF_N8N_PLUGIN_FILE', __FILE__ );
define( 'PF_N8N_PLUGIN_BASENAME', \plugin_basename( __FILE__ ) );
define( 'PF_N8N_PLUGIN_DIR', \plugin_dir_path( __FILE__ ) );
define( 'PF_N8N_PLUGIN_URL', \plugin_dir_url( __FILE__ ) );

// Autoload includes
require_once PF_N8N_PLUGIN_DIR . 'includes/helpers.php';
require_once PF_N8N_PLUGIN_DIR . 'includes/class-pf-n8n-plugin.php';
require_once PF_N8N_PLUGIN_DIR . 'includes/class-pf-n8n-logger.php';
require_once PF_N8N_PLUGIN_DIR . 'includes/class-pf-n8n-settings.php';

// Activation hook: ensure sensible defaults (logging disabled by default)
register_activation_hook( __FILE__, function() {
    $opts = get_option( PF_N8N\Settings::OPTION, [] );
    if ( ! is_array( $opts ) ) {
        $opts = [];
    }
    if ( ! isset( $opts['enable_logging'] ) ) {
        $opts['enable_logging'] = 0;
    }
    update_option( PF_N8N\Settings::OPTION, $opts );
} );

// Note: when hosted on WordPress.org translations are loaded automatically; no manual textdomain load required.

// Bootstrap
\add_action( 'plugins_loaded', function() {
	\PF_N8N\Plugin::get_instance();
} );

// Admin notice: warn when Elementor Pro is not active so site admins know the dependency
\add_action( 'admin_notices', function() {
	if ( ! \current_user_can( 'manage_options' ) ) {
		return;
	}

	// If Elementor Pro's main class is missing, show a warning
	if ( ! class_exists( '\\ElementorPro\\Plugin' ) ) {
		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo \esc_html__( 'n8n Webhook for Elementor requires Elementor Pro to be installed and active. Please install or activate Elementor Pro.', 'n8n-webhook-for-elementor-by-pixelfarben' );
		echo '</p></div>';
	}
} );

// Uninstall cleanup (kept for backwards compatibility). We also provide an uninstall.php for
// the WordPress.org repository which is the recommended approach for reliable uninstall handling.
\register_uninstall_hook( __FILE__, [ '\\PF_N8N\\Plugin', 'uninstall' ] );


