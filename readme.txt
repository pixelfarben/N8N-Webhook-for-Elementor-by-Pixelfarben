=== Pixelfarben — n8n Webhook for Elementor ===
Contributors: pixelfarben
Tags: elementor, elementor-pro, n8n, webhook
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later

Adds an n8n webhook action for Elementor Forms with optional HMAC signing, retries and privacy controls.

== Description ==
Adds an "n8n Trigger (Pixelfarben)" action for Elementor Forms to send submissions to an n8n webhook with optional Secret Key signature (HMAC), logging, retry/backoff, and privacy controls (IP anonymization and excluded fields).

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/n8n-webhook-for-elementor-by-pixelfarben` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure the settings under the Pixelfarben -> n8n admin page: set a default webhook URL and optional Secret Key.
4. In Elementor Forms, add the "n8n Trigger (Pixelfarben)" action to a form and configure per-form webhook, HMAC, headers, and field rules.

== Frequently Asked Questions ==
= Does this require Elementor Pro? =
Yes. This plugin integrates with Elementor Pro Forms and will show an admin notice if Elementor Pro is not active.

= How is the signature calculated? =
When enabled, the header `X-N8N-Signature: sha256=<signature>` is added, where `<signature>` is the HMAC-SHA256 of the JSON payload using the Secret Key configured in the plugin settings.

== Changelog ==
= 1.0.0 =
* Initial release

== Screenshots ==
1. Settings page
2. Logs view

== Dependencies ==
- Requires: Elementor Pro (version compatible with Elementor Pro Forms API). The plugin will show an admin notice if Elementor Pro is not active.

== Privacy ==
This plugin optionally logs webhook requests (disabled by default). Logged data may include truncated request bodies and response codes; IP addresses can be anonymized in settings. Do not enable logging on sites that collect highly sensitive personal data. Use the "Exclude fields" setting to avoid sending or logging sensitive fields (passwords, credit card numbers).

== Suggested plugin slug ==
For WordPress.org, use the slug `pixelfarben-n8n-webhook` (avoid starting slugs with trademarks).

== Plugin assets guidance ==
Place correctly sized plugin assets in the `assets/` directory when uploading to WordPress.org or SVN. Recommended files:
- `banner-772x250.png` (PNG, 772×250)
- `banner-1544x500.png` (optional high-res banner)
- `icon-128x128.png` (PNG, 128×128)
- `icon-256x256.png` (PNG, 256×256)
- Screenshots: `screenshot-1.png`, `screenshot-2.png`, ... (PNG, recommended 1240×900)

Make sure all images are GPL-compatible or created by you.

== Upgrade Notice ==
= 1.0.0 =
Initial release.


