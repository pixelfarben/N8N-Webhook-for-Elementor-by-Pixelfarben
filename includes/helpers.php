<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'pf_n8n_array_get' ) ) {
	function pf_n8n_array_get( $array, $key, $default = null ) {
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}
}

if ( ! function_exists( 'pf_n8n_anonymize_ip' ) ) {
	function pf_n8n_anonymize_ip( $ip ) {
		if ( empty( $ip ) ) {
			return '';
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts = explode( '.', $ip );
			$parts[3] = '0';
			return implode( '.', $parts );
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			// zero out the last 80 bits (~5 hextets) for a rough anonymization
			$hextets = explode( ':', $ip );
			for ( $i = 3; $i < count( $hextets ); $i++ ) {
				$hextets[ $i ] = '0000';
			}
			return implode( ':', $hextets );
		}
		return '';
	}
}

if ( ! function_exists( 'pf_n8n_truncate' ) ) {
	function pf_n8n_truncate( $string, $length = 500 ) {
		$string = (string) $string;
		if ( strlen( $string ) <= $length ) {
			return $string;
		}
		return substr( $string, 0, $length ) . '…';
	}
}


