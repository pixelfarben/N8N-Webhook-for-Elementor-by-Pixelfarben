Thank you for your interest in contributing to the Pixelfarben n8n Webhook for Elementor plugin.

This document explains how to contribute, how the repository is structured, and what to expect when you open issues or pull requests.

1. Where to contribute

- Bug reports and feature requests: open an Issue on this repository. Please include steps to reproduce, WordPress/Elementor versions, and PHP version.
- Pull requests: fork the repository and open a pull request against the `main` branch.

2. Code style and tests

- PHP: follow WordPress PHP coding standards. Use descriptive variable and function names.
- JavaScript (if present): follow standard ESLint rules. Keep code readable and documented.
- Add unit or integration tests for any new functionality where reasonable.

3. Branches and releases

- `main` is the development branch. We use semantic versioning for releases.
- Create topic branches named like `feature/short-description` or `fix/short-description`.

4. Commit messages

- Use clear, imperative commit messages. Prefix with the area when helpful, e.g. `Settings: sanitize inputs` or `Elementor action: verify nonce`.

5. Pull request checklist

- PR description with the problem, solution, and any migration notes.
- All new/changed strings are wrapped with translation functions and added to the POT file.
- No debugging code or secrets are included.
- Add or update documentation in `readme.txt` when user-visible behavior changes.

6. Local development

- Recommended: develop in a local WordPress environment (LocalWP, Docker, VVV, etc.) with Elementor Pro installed.
- Install plugin from the plugin folder (not the repo root) to mirror the SVN/distributable layout.

7. Tests & CI

- Currently no CI configured. Please include tests when adding significant features; we may add GitHub Actions later.

8. Code of conduct

- Be respectful and inclusive. The repository follows the Contributor Covenant Code of Conduct.

9. Contact

- For questions about the project, open an issue or contact `plugins@pixelfarben.de`.

Thank you for helping improve the plugin!


