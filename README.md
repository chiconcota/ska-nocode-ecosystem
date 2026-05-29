# Ska No-Code Ecosystem
> A professional, high-performance, and AI-native web application builder built on WordPress.

Ska No-Code Ecosystem is a decoupled micro-architecture designed to transform WordPress from a traditional CMS into a production-ready, visual application builder. By utilizing flat database tables, modern frontend state management, dynamic server-side expressions, and a zero-legacy-bloat canvas, Ska provides a modern software development environment for both developers and AI assistants.

---

## 🗺️ Architectural Ecosystem

The ecosystem is split into **4 decoupled plugins** and **1 clean canvas theme**, communicating asynchronously through WordPress hooks (`do_action` / `apply_filters`) to ensure strict isolation:

```
                  ┌─────────────────────────────────┐
                  │        Ska Blank Theme          │
                  │     (Zero Legacy CSS Canvas)    │
                  └────────────────┬────────────────┘
                                   │
┌──────────────────────────────────┼────────────────────────────────────────┐
│                             4 PLUGINS                                     │
├───────────────────┬──────────────┴──────┬─────────────────────────────────┤
│  Ska Data Pro     │  Ska Logic Engine   │   Ska No-Code Design            │
│  (Flat Database)  │ (Event & Expression)│ (Tailwind v4, Alpine v3, JIT )  │
└─────────┬─────────┘└──────────┬──────────┘└─────────────┬─────────────────┘
          │                     │                         │
          └─────────────────────┼─────────────────────────┘
                                │
                  ┌─────────────┴─────────────┐
                  │        Ska Bridge         │
                  │      (wordpress2nextjs)   │
                  └───────────────────────────┘
```

1. **Ska Blank Theme**
   * Acts as a pure blank canvas by stripping out all default WordPress block library styles and global inline CSS styles.
   * Offers a clean slate for custom frontend application renders.

2. **Ska No-Code Design**
   * Handles visual block rendering (atomic container, lists, icons, forms, images, buttons).
   * Provides compilation bridges including translation maps (`html2tailwind`).
   * Bundles the **Skapine Engine** (using Alpine.js store and directives) for instant interactive state management.
   * Integrates an editor workspace and a **Tailwind v4 JIT** compiler engine to prevent inline style bloat.
   * Theme builder, design token manager, modern design system.

3. **Ska Data Pro**
   * Abandons the slow and legacy `wp_postmeta` model.
   * Creates and queries high-performance custom flat tables (`ska_data_*`) using a built-in Schema Manager.
   * Handles Smart Object Blueprints and native JSON payload exports/imports.

4. **Ska Logic Engine**
   * Implements "The Trinity" event-driven flow: View (Events) ➔ Logic (Event pipelines) ➔ Data (Reads/Writes).
   * Evaluates dynamic code and data binding safely using the **SkaFX DSL** (Abstract Syntax Tree Parser).
   * Protects state and operations using strict Nonces and automated Data Healing mechanisms.

5. **Ska Bridge (Adapter)**
   * Exposes headless Rest API JSON endpoints to distribute layouts directly into external frameworks (like Next.js).

---

## 🛠️ Getting Started

### Prerequisites
* **Local Development Environment**: LocalWP, Docker (ddev/lando), or a standard LAMP/LEMP stack with PHP 8.2+ and MySQL/MariaDB.
* **Node.js**: v18+ and npm v10+.
* **Git**: To track and manage changes.

### Installation Steps
1. Clone the repository into your WordPress development root:
   ```bash
   git clone git@github.com:chiconcota/ska-nocode-ecosystem.git
   ```
2. Symlink or copy the plugins and theme folders to their respective directories:
   * Plugins ➔ `wp-content/plugins/`
   * Theme ➔ `wp-content/themes/`

3. Navigate to each package directory to install build dependencies and compile frontend assets:

   ```bash
   # Ska No-Code Design
   cd wp-content/plugins/ska-no-code-design
   npm install
   npm run build

   # Ska Data Pro
   cd ../ska-data-pro
   npm install
   npm run build

   # Ska Logic Engine
   cd ../ska-logic-engine
   npm install
   npm run build
   ```

4. Log in to the WordPress Admin dashboard and activate the plugins and theme in the following order:
   1. **Ska Data Pro**
   2. **Ska Logic Engine**
   3. **Ska No-Code Design**
   4. **Ska Bridge**
   5. **Ska Blank Theme** (as the active theme)

---

## 🤖 AI-Native Collaboration (Developer Note)

This project is built to be **AI-Native**, allowing AI agents (like Cursor, Copilot, Gemini) to work on the codebase side-by-side with human developers without losing architectural context.

We maintain two directories at the workspace root to guide AI models:
* [`.ska-ai/`](file://./.ska-ai): Contains the System Map, Architectural Decision Log, and Phase Manager roadmaps.
* [`.agent/`](file://./.agent): Contains context files and formatting rules (`wp-architect.md`, `ska-nocode-system.md`, `ska-docs-management.md`) which prompt the LLM to follow strict clean-code boundaries, PHP 8.2 typing, secure WP standards, and i18n specifications.

> [!NOTE]
> **Always notify your AI assistant** to read the rules in `.agent/rules/` and update `system_map.md` & `decision-log.md` whenever committing code changes.

---

## 🤝 Contributing
We welcome contributions from the community! Whether you are fixing a bug, adding new features, or refining documentation, please check out our [Contributing Guidelines](file://./CONTRIBUTING.md) to get started.

---

## 📄 License
This project is open-source and licensed under the **GNU GPLv3 (General Public License version 3)**. See the [LICENSE](file://./LICENSE) file for the full legal text.

