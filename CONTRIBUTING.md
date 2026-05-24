# Contributing to Ska No-Code Ecosystem

Thank you for your interest in contributing to the Ska No-Code Ecosystem! We welcome contributions from developers, designers, writers, and AI-native collaborators to help make WordPress a high-performance web application builder.

Please take a moment to review this document to understand our development workflow, coding standards, and guidelines.

---

## 🛠️ Development Setup

1. **Fork the Repository**: Fork this repository to your own GitHub account and clone it locally.
2. **Setup WordPress**: Install WordPress locally (using LocalWP, Docker, or your preferred environment).
3. **Symlink Packages**: Place the plugins and the theme inside your `wp-content/plugins/` and `wp-content/themes/` directories.
4. **Build Assets**: Run the following in each active plugin directory:
   ```bash
   npm install
   npm run build
   ```

---

## 🌿 Branching Strategy

We use a standard branching model. When submitting changes, please create a branch off `main` using the following naming conventions:
* `feat/your-feature-name` — for new features.
* `fix/bug-description` — for bug fixes.
* `docs/documentation-update` — for documentation changes.
* `refactor/code-improvement` — for codebase restructuring.

---

## 💻 Coding Standards

To maintain consistency and security, all contributions must strictly adhere to the following rules:

### 1. WordPress & PHP Standards
* **PHP Version**: Must support **PHP 8.2+** (use typed properties, union types, and modern match expressions where appropriate).
* **Strict Security (Non-Negotiable)**:
  * **Sanitize Inputs**: Always sanitize incoming data (e.g., `sanitize_text_field()`, `absint()`).
  * **Escape Outputs**: Escaping is mandatory before outputting HTML/JS (e.g., `esc_html()`, `esc_attr()`, `esc_url()`).
  * **Nonce Verification**: All form actions and REST/AJAX endpoints must verify nonces.
  * **SQL Queries**: Use `$wpdb->prepare` for raw SQL. Prefer `WP_Query` where possible.
* **Architecture**: Code logic must remain encapsulated inside actions/filters. Never execute logic in the global scope.

### 2. Frontend Standards
* **JavaScript**: Use Vanilla JS or Alpine.js. Do not introduce jQuery dependencies.
* **CSS & Tailwind**: All styling attributes should map to Tailwind CSS v4 utility classes. Avoid inline `<style>` blocks or hardcoded absolute styles unless dynamically generated.

### 3. Internationalization (i18n)
* **Default Language**: All user interface strings must be written in **English** by default.
* **i18n Functions**: Wrap all UI strings in standard WordPress translation functions:
  ```php
  __( 'My string', 'plugin-domain' )
  esc_html__( 'My string', 'plugin-domain' )
  ```

---

## 🤖 AI-Native Contribution Protocol

If you are using AI agents (such as Cursor, GitHub Copilot, or Gemini) to write code, please enforce the following requirements:

1. **Rules Alignment**: Instruct your AI agent to read the rules defined in [`.agent/rules/`](file://./.agent/rules/) before generating code.
2. **No Markdown Clutter**: Do not allow your AI to create random Markdown (`.md`) files in the root folder or elsewhere. All architectural files must reside in the four core buckets inside [`.ska-ai/`](file://./.ska-ai/).
3. **Docs Update & Git Conflicts Prevention**: Every commit that introduces architectural changes, database updates, or new hooks should update:
   * [`.ska-ai/1-overview/system_map.md`](file://./.ska-ai/1-overview/system_map.md) (Status & Recent Logs).
   * [`.ska-ai/2-memory/decision-log.md`](file://./.ska-ai/2-memory/decision-log.md) (Architectural Decisions).
   * ⚠️ **CRITICAL**: Do **NOT** modify or commit changes to [`.ska-ai/2-memory/checkpoint.md`](file://./.ska-ai/2-memory/checkpoint.md) or files under [`.ska-ai/1-overview/project-managers/`](file://./.ska-ai/1-overview/project-managers/). These files manage local session states and roadmap tracking; committing them will cause unnecessary Git merge conflicts.

---

## 📥 Submitting Pull Requests

1. **Write Clean Commits**: Keep commit messages concise, descriptive, and follow the conventional commit format (e.g., `feat(design): add padding inspector`).
2. **Run Tests**: Ensure your code does not throw PHP notices, warnings, or Javascript console errors. Enable `WP_DEBUG` in your `wp-config.php`.
3. **Submit the PR**: Submit your Pull Request targeting the `main` branch. Provide a detailed description of what the PR changes and how to manually verify it.

---

## 💬 Questions and Support

If you have questions about the codebase or architecture, please open an Issue with the `question` tag, or refer to our global [System Map](file://./.ska-ai/1-overview/system_map.md).
