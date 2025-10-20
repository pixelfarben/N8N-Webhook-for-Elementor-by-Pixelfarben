# n8n Webhook for Elementor by Pixelfarben

Adds an "n8n Trigger (Pixelfarben)" action for Elementor Forms to POST submissions to an n8n webhook with optional Secret Key signature, retries, logging, and privacy controls.

## Settings
- n8n Base URL
- Default Webhook URL (optional)
- Secret Key (optional) — must match the Secret Key set in the n8n trigger node
- Enable Logging
- Anonymize IP (logs only)

## Per-form controls
- Webhook URL (overrides default)
- Secret Key (enable verification)
- Extra headers (key:value per line)
- Exclude fields (comma-separated)
- Key rename rules (from:to per line)

## Signature
If Secret Key is set and enabled per form, the header `X-N8N-Signature: sha256=<signature>` is included, computed over the raw JSON string. The n8n trigger node will verify this signature using the same Secret Key.

### Payload
```
{
  "form_id": 123,
  "form_name": "Contact",
  "page_url": "https://example.com/contact",
  "submitted": "2025-01-01 12:34:56",
  "fields": {"name":"John","email":"john@example.com"}
}
```

### Retries & Logging
- Timeout: 5s. Retries: up to 2 with backoff (≈500ms → 1500ms).
- Logs: Pixelfarben → n8n Logs. Shows latest 50; export as JSON; clear logs.

### Privacy
- Anonymize IP in logs (no IP sent to n8n by default).
- Exclude fields are removed before sending.

### Sample n8n Workflow
See `sample-workflow/pf-n8n-sample-workflow.json` for a minimal Webhook → If → Set example.

### Localization
- Strings are translatable (textdomain `n8n-webhook-for-elementor-by-pixelfarben`). POT in `languages/`.

### Security & QA
- Nonces for admin actions, capability checks (`manage_options`).
- Sanitization/escaping for settings and outputs.
- Tested on PHP 7.4/8.x, WP 6.x, Elementor Pro.


