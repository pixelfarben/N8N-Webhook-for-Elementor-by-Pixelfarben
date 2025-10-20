<?php
namespace PF_N8N;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	/** @var Plugin */
	private static $instance;

	/**
	 * Singleton
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->hooks();
	}

	private function hooks() {
		// Admin menus and settings
		\add_action( 'admin_menu', [ Settings::class, 'register_menus' ] );
		\add_action( 'admin_init', [ Settings::class, 'register_settings' ] );

		// Elementor integration: register on the specific Forms hook so timing is correct
		\add_action( 'elementor_pro/forms/actions/register', function( $actions ) {
			require_once PF_N8N_PLUGIN_DIR . 'includes/class-pf-n8n-elementor-action.php';
			if ( class_exists( '\\PF_N8N\\Elementor_Action' ) ) {
				$instance = new \PF_N8N\Elementor_Action();
				if ( method_exists( $actions, 'register_action' ) ) {
					$actions->register_action( $instance );
				} elseif ( method_exists( $actions, 'register' ) ) {
					$actions->register( $instance );
				}
			}
		}, 10 );
	}

	/**
	 * Uninstall callback for register_uninstall_hook
	 */
	public static function uninstall() {
		\delete_option( 'pf_n8n_settings' );
		\delete_option( 'pf_n8n_logs' );
	}
}


