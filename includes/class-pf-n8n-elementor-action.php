<?php
namespace PF_N8N;

use ElementorPro\Modules\Forms\Classes\Action_Base;
use ElementorPro\Modules\Forms\Classes\Form_Record;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Elementor_Action extends Action_Base {
	public static function register() {
        // Ensure Elementor Pro Forms classes are available
        if ( ! class_exists( '\\ElementorPro\\Modules\\Forms\\Classes\\Action_Base' ) ) {
            return;
        }
        \add_action( 'elementor_pro/forms/actions/register', function( $actions ) {
            $instance = new self();
            if ( method_exists( $actions, 'register_action' ) ) {
                $actions->register_action( $instance );
            } else {
                // Backward/alternate API
                if ( method_exists( $actions, 'register' ) ) {
                    $actions->register( $instance );
                }
            }
        } );
	}

	public function get_name() {
		return 'pf_n8n_trigger';
	}

	public function get_label() {
		return \__( 'n8n Trigger (Pixelfarben)', 'n8n-webhook-for-elementor-by-pixelfarben' );
	}

	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'pf_n8n_section',
		[ 'label' => \__( 'Pixelfarben → n8n', 'n8n-webhook-for-elementor-by-pixelfarben' ), 'tab' => 'content' ]
		);

		$widget->add_control(
			'pf_n8n_webhook',
			[
			'label' => \__( 'Webhook URL (overrides default)', 'n8n-webhook-for-elementor-by-pixelfarben' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'https://n8n.example.com/webhook/...',
				'label_block' => true,
			]
		);

		$widget->add_control(
			'pf_n8n_enable_hmac',
			[
			'label' => \__( 'Secret Key (enable verification)', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'label_on' => \__( 'Yes', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			'label_off' => \__( 'No', 'n8n-webhook-for-elementor-by-pixelfarben' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'label_block' => true,
				'description' => \__( 'When enabled, requests are signed with the Secret Key set in Pixelfarben → n8n Settings. It must match the Secret Key in the n8n trigger node.', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			]
		);

		$widget->add_control(
			'pf_n8n_extra_headers',
			[
			'label' => \__( 'Extra headers (key:value per line)', 'n8n-webhook-for-elementor-by-pixelfarben' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'rows' => 5,
				'placeholder' => "X-Custom: abc\nX-Env: prod",
				'label_block' => true,
			]
		);

		$widget->add_control(
			'pf_n8n_exclude_fields',
			[
			'label' => \__( 'Exclude fields (comma-separated)', 'n8n-webhook-for-elementor-by-pixelfarben' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'password,credit_card',
				'label_block' => true,
			]
		);

		$widget->add_control(
			'pf_n8n_key_mapping',
			[
			'label' => \__( 'Key rename rules (from:to per line)', 'n8n-webhook-for-elementor-by-pixelfarben' ),
				'type' => \Elementor\Controls_Manager::TEXTAREA,
				'rows' => 4,
				'placeholder' => "email:contact_email\nname:full_name",
				'label_block' => true,
			]
		);

		$widget->end_controls_section();
	}

	public function on_export( $element ) {}

	public function run( $record, $ajax_handler ) {
		if ( ! $record instanceof Form_Record ) {
			return;
		}

		$raw_fields = $record->get( 'fields' );
		$settings = get_option( Settings::OPTION, [] );
		$form_settings = $record->get( 'form_settings' );

		$exclude_list = [];
		if ( ! empty( $form_settings['pf_n8n_exclude_fields'] ) ) {
			$exclude_list = array_filter( array_map( 'trim', explode( ',', (string) $form_settings['pf_n8n_exclude_fields'] ) ) );
		}

		$fields_assoc = [];
		foreach ( $raw_fields as $field ) {
			$key_source = '';
			if ( isset( $field['title'] ) && $field['title'] ) {
				$key_source = (string) $field['title'];
			} elseif ( isset( $field['id'] ) && $field['id'] ) {
				$key_source = (string) $field['id'];
			}
			$key = sanitize_key( strtolower( str_replace( ' ', '_', $key_source ) ) );
			if ( in_array( $key, $exclude_list, true ) ) {
				continue;
			}
			$value = isset( $field['value'] ) ? $field['value'] : '';
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}
			$fields_assoc[ $key ] = $value;
		}

		// Key mapping rules
		if ( ! empty( $form_settings['pf_n8n_key_mapping'] ) ) {
			$lines = explode( "\n", (string) $form_settings['pf_n8n_key_mapping'] );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( $line === '' || strpos( $line, ':' ) === false ) {
					continue;
				}
				list( $from, $to ) = array_map( 'trim', explode( ':', $line, 2 ) );
				$from = sanitize_key( strtolower( str_replace( ' ', '_', $from ) ) );
				$to = sanitize_key( strtolower( str_replace( ' ', '_', $to ) ) );
				if ( $from && $to && isset( $fields_assoc[ $from ] ) ) {
					$fields_assoc[ $to ] = $fields_assoc[ $from ];
					unset( $fields_assoc[ $from ] );
				}
			}
		}

		$form_id = (int) $record->get_form_settings( 'id' );
		$form_name = (string) $record->get_form_settings( 'form_name' );
		// Prefer the WordPress referer helper instead of trusting raw POST data.
		$page_url = \wp_get_referer();
		$page_url = $page_url ? \esc_url_raw( \wp_unslash( $page_url ) ) : '';

		// Best-effort nonce verification: Elementor may include nonces in requests. If a nonce is present,
		// verify it to avoid processing forged requests. If no nonce is present, continue (Elementor may
		// validate the request via its own mechanisms).
		if ( ! empty( $_REQUEST['_wpnonce'] ) && function_exists( 'wp_verify_nonce' ) ) {
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? \wp_unslash( $_REQUEST['_wpnonce'] ) : '';
			$nonce_ok = false;
			// try common Elementor/nonced actions
			if ( \wp_verify_nonce( $nonce, 'elementor_pro_forms' ) || \wp_verify_nonce( $nonce, 'elementor_ajax' ) || \wp_verify_nonce( $nonce, 'wp_rest' ) ) {
				$nonce_ok = true;
			}
			if ( ! $nonce_ok ) {
				// Do not process if a nonce is present but invalid
				return;
			}
		}
		$submitted = \current_time( 'mysql' );

		$payload = [
			'form_id' => $form_id,
			'form_name' => $form_name,
			'page_url' => $page_url,
			'submitted' => $submitted,
			'fields' => $fields_assoc,
		];

		$json = \wp_json_encode( $payload );
		$webhook = '';
		if ( ! empty( $form_settings['pf_n8n_webhook'] ) ) {
			$webhook = \esc_url_raw( $form_settings['pf_n8n_webhook'] );
		} elseif ( ! empty( $settings['default_webhook'] ) ) {
			$webhook = \esc_url_raw( $settings['default_webhook'] );
		}
		if ( empty( $webhook ) ) {
			Logger::log( [
				'form_id' => $form_id,
				'form_name' => $form_name,
				'status' => 'no-webhook',
				'request_body' => \pf_n8n_truncate( $json ),
			] );
			return;
		}

		$headers = [ 'Content-Type' => 'application/json' ];
		// extra headers
		if ( ! empty( $form_settings['pf_n8n_extra_headers'] ) ) {
			$lines = explode( "\n", (string) $form_settings['pf_n8n_extra_headers'] );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( $line === '' || strpos( $line, ':' ) === false ) {
					continue;
				}
				list( $k, $v ) = array_map( 'trim', explode( ':', $line, 2 ) );
				$headers[ $k ] = $v;
			}
		}

		$enable_hmac_form = ! empty( $form_settings['pf_n8n_enable_hmac'] ) && $form_settings['pf_n8n_enable_hmac'] === 'yes';
		$secret = isset( $settings['hmac_secret'] ) ? (string) $settings['hmac_secret'] : '';
		if ( $enable_hmac_form && $secret !== '' ) {
			$signature = hash_hmac( 'sha256', $json, $secret );
			$headers['X-N8N-Signature'] = 'sha256=' . $signature;
		}

		$attempts = 0;
		$max_attempts = 3; // first try + 2 retries
		$delay_ms = 500;
		$status_code = null;
		$response_body = '';
		$start = microtime( true );

		while ( $attempts < $max_attempts ) {
			$attempts++;
			$resp = \wp_remote_post( $webhook, [
				'timeout' => 5,
				'headers' => $headers,
				'body' => $json,
			] );

			if ( \is_wp_error( $resp ) ) {
				Logger::log( [
					'form_id' => $form_id,
					'form_name' => $form_name,
					'status' => 'error',
					'error' => $resp->get_error_message(),
					'request_body' => \pf_n8n_truncate( $json ),
					'webhook' => $webhook,
				] );
			} else {
				$status_code = \wp_remote_retrieve_response_code( $resp );
				$response_body = (string) \wp_remote_retrieve_body( $resp );
				if ( $status_code >= 200 && $status_code < 300 ) {
					break; // success
				} else {
					Logger::log( [
						'form_id' => $form_id,
						'form_name' => $form_name,
						'status' => $status_code,
						'response' => \pf_n8n_truncate( $response_body ),
						'request_body' => \pf_n8n_truncate( $json ),
						'webhook' => $webhook,
					] );
				}
			}

			if ( $attempts < $max_attempts ) {
				usleep( $delay_ms * 1000 );
				$delay_ms = (int) round( $delay_ms * 3 ); // 500ms -> 1500ms (approx exponential)
			}
		}

		$duration_ms = (int) round( ( microtime( true ) - $start ) * 1000 );
		Logger::log( [
			'form_id' => $form_id,
			'form_name' => $form_name,
			'status' => $status_code ? $status_code : 'error',
			'duration_ms' => $duration_ms,
			'response' => \pf_n8n_truncate( $response_body ),
			'request_body' => \pf_n8n_truncate( $json ),
			'webhook' => $webhook,
		] );
	}
}


