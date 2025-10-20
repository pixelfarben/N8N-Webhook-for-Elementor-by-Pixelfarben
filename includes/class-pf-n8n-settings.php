<?php
namespace PF_N8N;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {
	const OPTION = 'pf_n8n_settings';

	public static function register_menus() {

		\add_menu_page(
			\__( 'Pixelfarben', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			\__( 'Pixelfarben', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			'manage_options',
			'pf-n8n',
			[ __CLASS__, 'render_settings_page' ],
			'dashicons-share',
			58
		);

		\add_submenu_page(
			'pf-n8n',
			\__( 'n8n Settings', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			\__( 'n8n Settings', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			'manage_options',
			'pf-n8n',
			[ __CLASS__, 'render_settings_page' ]
		);

		\add_submenu_page(
			'pf-n8n',
			\__( 'n8n Logs', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			\__( 'n8n Logs', 'n8n-webhook-for-elementor-by-pixelfarben' ),
			'manage_options',
			'pf-n8n-logs',
			[ __CLASS__, 'render_logs_page' ]
		);
	}

	public static function register_settings() {
		\register_setting( 'pf_n8n_settings_group', self::OPTION, [ __CLASS__, 'sanitize' ] );

		\add_settings_section( 'pf_general', \__( 'General', 'n8n-webhook-for-elementor-by-pixelfarben' ), '__return_false', 'pf-n8n' );

		\add_settings_field( 'base_url', \__( 'n8n Base URL', 'n8n-webhook-for-elementor-by-pixelfarben' ), [ __CLASS__, 'field_text' ], 'pf-n8n', 'pf_general', [ 'key' => 'base_url', 'placeholder' => 'https://n8n.example.com' ] );
		\add_settings_field( 'default_webhook', \__( 'Default Webhook URL (optional)', 'n8n-webhook-for-elementor-by-pixelfarben' ), [ __CLASS__, 'field_text' ], 'pf-n8n', 'pf_general', [ 'key' => 'default_webhook', 'placeholder' => 'https://n8n.example.com/webhook/...' ] );
		\add_settings_field( 'hmac_secret', \__( 'Secret Key', 'n8n-webhook-for-elementor-by-pixelfarben' ), [ __CLASS__, 'field_password' ], 'pf-n8n', 'pf_general', [ 'key' => 'hmac_secret', 'description' => \__( 'Must match the Secret Key set in the n8n trigger node.', 'n8n-webhook-for-elementor-by-pixelfarben' ) ] );
		\add_settings_field( 'enable_logging', \__( 'Enable Logging', 'n8n-webhook-for-elementor-by-pixelfarben' ), [ __CLASS__, 'field_checkbox' ], 'pf-n8n', 'pf_general', [ 'key' => 'enable_logging' ] );
		\add_settings_field( 'anonymize_ip', \__( 'Anonymize IP (logs only)', 'n8n-webhook-for-elementor-by-pixelfarben' ), [ __CLASS__, 'field_checkbox' ], 'pf-n8n', 'pf_general', [ 'key' => 'anonymize_ip' ] );
	}

	public static function sanitize( $input ) {
		$output = [];
		$output['base_url'] = isset( $input['base_url'] ) ? \esc_url_raw( $input['base_url'] ) : '';
		$output['default_webhook'] = isset( $input['default_webhook'] ) ? \esc_url_raw( $input['default_webhook'] ) : '';
		$output['hmac_secret'] = isset( $input['hmac_secret'] ) ? \sanitize_text_field( $input['hmac_secret'] ) : '';
		$output['enable_logging'] = ! empty( $input['enable_logging'] ) ? 1 : 0;
		$output['anonymize_ip'] = ! empty( $input['anonymize_ip'] ) ? 1 : 0;
		return $output;
	}

	public static function field_text( $args ) {
		$options = \get_option( self::OPTION, [] );
		$key = $args['key'];
		$val = isset( $options[ $key ] ) ? $options[ $key ] : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		echo '<input type="url" class="regular-text" name="' . \esc_attr( self::OPTION . '[' . $key . ']' ) . '" value="' . \esc_attr( $val ) . '" placeholder="' . \esc_attr( $placeholder ) . '" />';
	}

	public static function field_password( $args ) {
		$options = \get_option( self::OPTION, [] );
		$key = $args['key'];
		$val = isset( $options[ $key ] ) ? $options[ $key ] : '';
		echo '<input type="password" class="regular-text" name="' . \esc_attr( self::OPTION . '[' . $key . ']' ) . '" value="' . \esc_attr( $val ) . '" autocomplete="new-password" />';
		if ( ! empty( $args['description'] ) ) {
			echo '<p class="description">' . \esc_html( $args['description'] ) . '</p>';
		}
	}

	public static function field_checkbox( $args ) {
		$options = \get_option( self::OPTION, [] );
		$key = $args['key'];
		$checked_attr = \checked( 1, isset( $options[ $key ] ) ? $options[ $key ] : 0, false );
		echo '<label><input type="checkbox" name="' . \esc_attr( self::OPTION . '[' . $key . ']' ) . '" value="1" ' . \esc_attr( $checked_attr ) . ' /> ' . \esc_html__( 'Enabled', 'n8n-webhook-for-elementor-by-pixelfarben' ) . '</label>';
	}

	public static function render_settings_page() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo \esc_html__( 'Pixelfarben → n8n Settings', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></h1>
			<form method="post" action="options.php">
				<?php \settings_fields( 'pf_n8n_settings_group' ); ?>
				<?php \do_settings_sections( 'pf-n8n' ); ?>
				<?php \submit_button(); ?>
			</form>
			<p><?php echo \esc_html__( 'Privacy: IP addresses in logs can be anonymized. Excluded fields are not sent to n8n. Logging is disabled by default on fresh install.', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></p>
		</div>
		<?php
	}

	public static function render_logs_page() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle actions
		if ( isset( $_POST['pf_n8n_clear_logs'] ) && \check_admin_referer( 'pf_n8n_logs_actions', 'pf_n8n_logs_nonce' ) ) {
			Logger::clear();
			\add_settings_error( 'pf_n8n_logs', 'pf_n8n_logs_cleared', \__( 'Logs cleared.', 'n8n-webhook-for-elementor-by-pixelfarben' ), 'updated' );
		}
		if ( isset( $_POST['pf_n8n_export_logs'] ) && \check_admin_referer( 'pf_n8n_logs_actions', 'pf_n8n_logs_nonce' ) ) {
			$logs = Logger::get_last( 50 );
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=pf-n8n-logs.json' );
			echo \wp_json_encode( $logs );
			exit;
		}

		$logs = Logger::get_last( 50 );
		\settings_errors( 'pf_n8n_logs' );
		?>
		<div class="wrap">
			<h1><?php echo \esc_html__( 'Pixelfarben → n8n Logs', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></h1>
			<form method="post">
				<?php \wp_nonce_field( 'pf_n8n_logs_actions', 'pf_n8n_logs_nonce' ); ?>
				<?php \submit_button( \__( 'Export JSON', 'n8n-webhook-for-elementor-by-pixelfarben' ), 'secondary', 'pf_n8n_export_logs', false ); ?>
				<?php \submit_button( \__( 'Clear Logs', 'n8n-webhook-for-elementor-by-pixelfarben' ), 'delete', 'pf_n8n_clear_logs', false, [ 'onclick' => "return confirm('" . \esc_js( \__( 'Are you sure you want to clear logs?', 'n8n-webhook-for-elementor-by-pixelfarben' ) ) . "');" ] ); ?>
			</form>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php echo \esc_html__( 'Time', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Form', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Status', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Duration (ms)', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Request Body', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Response', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
						<th><?php echo \esc_html__( 'Webhook', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="6"><?php echo \esc_html__( 'No logs yet.', 'n8n-webhook-for-elementor-by-pixelfarben' ); ?></td></tr>
					<?php else : foreach ( $logs as $log ) : ?>
					<tr>
						<td><?php echo \esc_html( pf_n8n_array_get( $log, 'time', '' ) ); ?></td>
						<td><?php echo \esc_html( trim( pf_n8n_array_get( $log, 'form_name', '' ) . ' #' . pf_n8n_array_get( $log, 'form_id', '' ) ) ); ?></td>
						<td><?php echo \esc_html( pf_n8n_array_get( $log, 'status', '' ) ); ?></td>
						<td><?php echo \esc_html( (string) pf_n8n_array_get( $log, 'duration_ms', '' ) ); ?></td>
						<td><code><?php echo \esc_html( pf_n8n_array_get( $log, 'request_body', '' ) ); ?></code></td>
						<td><code><?php echo \esc_html( pf_n8n_array_get( $log, 'response', '' ) ); ?></code></td>
						<td><code><?php echo \esc_html( pf_n8n_array_get( $log, 'webhook', '' ) ); ?></code></td>
					</tr>
					<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}


