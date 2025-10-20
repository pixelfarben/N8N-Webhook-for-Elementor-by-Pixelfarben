Plugin assets to include for WordPress.org

Place the following files in the top-level `assets/` directory when preparing the WordPress.org plugin assets (these are *not* required inside the plugin folder itself):

- `banner-772x250.png` — required banner for plugin directory (PNG, 772×250)
- `banner-1544x500.png` — optional high-resolution banner (PNG, 1544×500)
- `icon-128x128.png` — required icon (PNG, 128×128)
- `icon-256x256.png` — optional larger icon (PNG, 256×256)
- `screenshot-1.png`, `screenshot-2.png`, ... — screenshots (PNG, recommended 1240×900)

Guidance:
- Keep total plugin zip < 10 MB for the initial upload.
- Do not include `node_modules`, `tests`, or large documentation files in the distributable.
- Ensure all images are either created by you or licensed compatibly with GPL.



