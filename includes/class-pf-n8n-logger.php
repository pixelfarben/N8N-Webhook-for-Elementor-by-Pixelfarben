<?php
namespace PF_N8N;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {
	const OPTION = 'pf_n8n_logs';
	const MAX_LOGS = 200; // keep a rolling window

	public static function log( array $entry ) {
		$settings = \get_option( 'pf_n8n_settings', [] );
		$enable_logging = isset( $settings['enable_logging'] ) ? (bool) $settings['enable_logging'] : false;
		if ( ! $enable_logging ) {
			return;
		}

		$logs = \get_option( self::OPTION, [] );
		if ( ! is_array( $logs ) ) {
			$logs = [];
		}

		$entry['time'] = isset( $entry['time'] ) ? $entry['time'] : \current_time( 'mysql' );
		$client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? \sanitize_text_field( \wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( ! empty( $settings['anonymize_ip'] ) ) {
			$client_ip = \pf_n8n_anonymize_ip( $client_ip );
		}
		$entry['client_ip'] = $client_ip;

		$logs[] = $entry;
		if ( count( $logs ) > self::MAX_LOGS ) {
			$logs = array_slice( $logs, -1 * self::MAX_LOGS );
		}
		\update_option( self::OPTION, $logs, false );
	}

	public static function get_last( $limit = 50 ) {
		$logs = \get_option( self::OPTION, [] );
		if ( ! is_array( $logs ) ) {
			$logs = [];
		}
		return array_slice( array_reverse( $logs ), 0, $limit );
	}

	public static function clear() {
		\delete_option( self::OPTION );
	}
}


