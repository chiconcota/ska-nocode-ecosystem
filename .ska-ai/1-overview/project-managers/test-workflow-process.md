# E2E Test Workflow: Ska Link Engine (Milestone 4)

> [!NOTE]
> This document provides the standard procedure and scenarios for QA testing of the Ska Link Engine system on both Block-Level (Container, Image, Button) and Inline (RichText format), focusing specifically on seamless interoperability with the **Ska Loop Engine**.

## Objectives
1. **SEO Compliance:** Ensure all links render native `<a href="...">` tags during Server-Side Rendering (SSR). No JS-based redirects allowed.
2. **Flat DOM Integrity:** Ensure "Morphing" (transforming tagNames to `<a>` for Containers/Buttons) operates correctly without generating redundant wrapper divs.
3. **Decoupled Hydration:** Ensure Mustache `{{key}}` placeholders output by the Link Engine are resolved (hydrated) accurately by the Loop Engine on the Frontend without causing N+1 Queries.

---

## 🧪 Test Cases

### Test Case 1: Static Link on Image & Container
**Status:** `[ ] Pending Test`

**Steps (Gutenberg Editor):**
1. Drag and drop the `ska-builder/image` block into the editor and select an image.
2. Open the **Link Settings** tab in the right Inspector panel, enter a static URL (e.g., `https://wp.org`), and check the `Open in new tab` option.
3. Drag and drop the `ska-builder/container` block.
4. Open the **Link Settings** tab for the Container, enter a static URL (e.g., `/contact`), and leave the target as default (`_self`).
5. Save/Update the page.

**Expected Results at Frontend (Inspect via Element Inspector):**
- **Image:** The native `<img>` tag must be wrapped inside an `<a href="https://wp.org" target="_blank" rel="noopener noreferrer">` tag.
- **Container:** The root element of the container (e.g., `<div>` or `<section>`) must automatically transform into an `<a href="/contact" target="_self">` tag while preserving all its original Tailwind classes (flat DOM preserved).

---

### Test Case 2: System Dynamic Link on Button
**Status:** `[ ] Pending Test`

**Steps (Gutenberg Editor):**
1. Drag and drop the `ska-builder/button` block.
2. Open the **Link Settings** tab, and toggle the **Dynamic Link** feature on.
3. Choose the source as `System` and select the key `Home URL`.
4. Save/Update the page.

**Expected Results at Frontend (Inspect via Element Inspector):**
- The button block should not render a `<button>` tag. It must render as an `<a>` tag containing the button's Tailwind classes, with its `href` attribute pointing to the site's home page (e.g., `https://your-domain.local/`).
- Extra attributes unrelated to the dynamic link (such as `data-dynamic-source`) must be stripped from the HTML output.

---

### Test Case 3: Loop Dynamic Link on Inline Text & Block-Level (Advanced)
**Status:** `[ ] Pending Test`

**Steps (Gutenberg Editor):**
1. Open a page template that supports the **Ska Loop Engine** (e.g., a post list or `ska-builder/list` block).
2. **Inline Testing:** Add a `ska-builder/text` block inside the loop.
   - Highlight any word/phrase, click the Link icon (Ska Dynamic Link) on the floating toolbar.
   - Enable **Dynamic Link**, choose source `Loop`, and set key to `post_url` (or the respective link variable).
3. **Block-Level Testing:** Add a `ska-builder/container` (or image) inside the loop.
   - Open the Link Settings for this block, set the URL via Dynamic Link -> Source `Loop` -> Key `post_url`.
4. Save/Update the page.

**Expected Results at Frontend (Inspect via Element Inspector):**
- The HTML SSR output from the PHP renderer (before loop execution) should contain `<a href="{{post_url}}">`.
- After hydration by the Loop Engine, the `{{post_url}}` placeholder must be interpolated into the actual link of each object inside the loop (e.g., `/post-1/`, `/post-2/`).
- Generating these links must not increase database queries (Check with Query Monitor to ensure Zero N+1 Queries).

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Container is nested with two `<a href>` tags** | Morphing logic of `tagName` conflicts with wrapper generation. | Check `render.php` of `ska-container`, ensure only one root tag is rendered and only `$tagName = 'a'` is changed instead of wrapping. |
| **Inline `<a>` tags expose garbage attributes (`data-dynamic-source`)** | The regex parser in `resolve_inline_links` missed stripping the attribute. | Open `class-dynamic-data.php` and update the `preg_replace` logic to remove all `data-dynamic-*="..."` strings after resolving the `href`. |
| **Loop dynamic link prints raw `{{post_url}}` string on the screen** | The link placeholder is generated after the Loop Engine performs hydration. | Check the sequence of hook filters on `the_content` or re-evaluate regex interpolation on the final Loop output. |

---

# E2E Test Workflow: Ska Theme Builder (Milestone 5)

> [!NOTE]
> This document provides the QA verification procedure for the Theme Builder system, covering the isolated editor iframe environment, the Dual-Table storage mechanism, and default template rendering interception using the Smart Virtual Wrapper.

## Objectives
1. **Isolated Editor & Dual-Table:** Ensure template creation/editing runs independently within an iframe, and data is accurately partitioned (metadata stored in `ska_data_sys_theme_templates`, HTML content in `ska_data_sys_organisms`). No residue should be written to `wp_posts`.
2. **Smart Virtual Wrapper:** Verify that the system intercepts the current active URL and overrides the default theme's Header/Footer/Body with the builder's designed organisms.
3. **Display Conditions (Rule Builder):** Validate that the backend condition parser evaluates display rules (Include/Exclude rules like is_front_page, is_single, etc.) accurately.

---

## 🧪 Test Cases

### Test Case 1: Create & Edit Template in Iframe
**Status:** `[ ] Pending Test`

**Steps:**
1. Go to WP Admin -> Ska Builder -> Theme Builder.
2. Click "Create New Template", name it "Header Test", Location: `Header`, Conditions: Leave blank (Default: Entire Site). Click Save.
3. Click "Open Editor" for the newly created template. The browser should load a full-screen iframe.
4. Drag and drop blocks into the editor (e.g., Container, Heading, Image), then click Save on the iframe toolbar.
5. Inspect the database (tables `ska_data_sys_theme_templates` and `ska_data_sys_organisms`) to confirm matching IDs and valid content write. Ensure no post records are left in `wp_posts`.

**Expected Results:**
- No JS Console errors during postMessage communication between parent and iframe window.
- The `organism_id` in the template table references the correct record in the organism table containing the designed HTML.

---

### Test Case 2: Smart Virtual Wrapper Template Interception
**Status:** `[ ] Pending Test`

**Steps:**
1. Open the homepage of the website in a guest browser session (logged out).
2. Note the default theme Header/Footer (e.g., Twenty Twenty-Four).
3. Ensure the "Header Test" template created in Test Case 1 is set to **Active**.
4. Reload the homepage.

**Expected Results:**
- The default theme Header disappears and is replaced by the designed header layout.
- The Footer and Body contents (if no template applies) render normally or fallback safely.
- Check Query Monitor to ensure the `template_include` hook (Priority 99) is intercepted with no errors.

---

### Test Case 3: Rule Builder (Complex Display Conditions)
**Status:** `[ ] Pending Test`

**Steps:**
1. Navigate to Theme Builder, create a new template named "Promo Banner", Location: `Header`.
2. Open "Edit Settings" for this template and click Add Rule.
3. Define two rules:
   - `Include` -> `Front Page`
   - `Exclude` -> `Search Results`
4. Save settings, open the editor, and design a distinct banner (e.g., red background banner).
5. Activate the template.

**Expected Results at Frontend:**
- Homepage: "Promo Banner" appears.
- Single Post: "Promo Banner" **does not** appear.
- Search Page (e.g., `/?s=test`): Even though search pages are a type of archive, the Exclude rule overrides and the banner **does not** appear.

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Layout does not override the existing theme** | The active theme does not invoke standard WP template tags. | Ensure the theme calls `wp_head()`, `wp_footer()`, and standard templates. Verify `template_include` filter is not overridden by another plugin with priority > 99. |
| **Display conditions evaluate incorrectly on homepage** | Confusion between `is_front_page()` and `is_home()`. | The backend consolidates `is_front_page() || is_home()` for the Homepage rule. Verify the settings in Settings > Reading of WP if issues persist. |
| **Blank screen after saving the editor** | JIT Compiler failed to retrieve HTML Cache. | Check `ska_data_sys_organisms` database table, ensure the `html_content` field has data. Trace the POST payload in Network Tab of DevTools during save. |

---

# E2E Test Workflow: Ska Dark Mode Engine (Phase 4.4)

> [!NOTE]
> This document details the E2E verification workflow for the Dark Mode Engine. The goal is to ensure smooth state transitions (via Alpine.js), correct CSS generation (Tailwind JIT), local persistence (localStorage), and eliminate FOUC (Flash of Unstyled Content) during page load.

## Objectives
1. **State & CSS Compilation:** Toggling changes the state `$store.skaTheme.isDark` and the JIT compiler produces CSS rules prefixed with `dark:`.
2. **Persistence & Anti-FOUC:** The dark mode state persists in localStorage and restores instantly upon reloading without causing flashes of light background.
3. **Reactive UI:** Interactive elements adapt responsively to dark mode states (e.g., changing icons based on mode).

---

## 🧪 Test Cases

### Test Case 1: Basic Dark Mode Toggle & JIT CSS
**Status:** `[ ] Pending Test`

**Steps (Gutenberg Editor):**
1. Insert a `ska-builder/container`. Add Tailwind classes: `bg-white dark:bg-slate-900 transition-colors duration-300`.
2. Inside the container, drop a `ska-builder/text` block. Add classes: `text-slate-900 dark:text-white`. Enter text: "Dark Mode Status".
3. Add a `ska-builder/button` into the container.
4. Under the Button Inspector, set the Action to **Toggle Dark Mode**.
5. Save/Update the page and check the Frontend.

**Expected Results at Frontend:**
- Default mode is Light (white background, black text).
- Clicking the Toggle button transitions the background to `bg-slate-900` and text to white.
- The `<html>` element dynamically gains/loses the `dark` class.
- The stylesheet `<style id='ska-jit-styles'>` contains definitions for `.dark .dark\:bg-slate-900` and `.dark .dark\:text-white`.

---

### Test Case 2: LocalStorage Persistence & Anti-FOUC
**Status:** `[ ] Pending Test`

**Steps:**
1. Continuing from Test Case 1, verify the interface is set to **Dark Mode = ON**.
2. Press **F5 (Reload page)**.
3. Observe the initial rendering frame before assets fully load.

**Expected Results at Frontend:**
- The page must render dark **instantly** without a transient flash of light background (Zero FOUC).
- DevTools -> Application -> Local Storage must hold a `ska_dark_mode` key set to `dark` or `true`.
- Page Source must show the inline Anti-FOUC block script placed at the very top of `<head>`.

---

### Test Case 3: Advanced Reactive UI (Conditional Rendering Icon)
**Status:** `[ ] Pending Test`

**Steps (Gutenberg Editor):**
1. Add two `ska-builder/icon` blocks to the canvas:
   - Icon 1 (Sun): Add Alpine attribute `x-show` set to `!$store.skaTheme.isDark`.
   - Icon 2 (Moon): Add Alpine attribute `x-show` set to `$store.skaTheme.isDark`.
2. Save and test on the Frontend.

**Expected Results at Frontend:**
- Under Light Mode, only the Sun icon displays.
- Toggling Dark Mode hides the Sun icon and renders the Moon icon instantly without reload.

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Toggle button has no effect** | JS Core or Alpine Store failed to load. | Check console for `Alpine is not defined` or `$store.skaTheme is undefined` errors. Ensure `ska-frontend.js` is enqueued. |
| **`<html>` has `dark` class but color stays light** | Tailwind JIT failed to compile `dark:` modifier rules. | Check regex compiler in `class-style-manager.php`, verify modifier detection supports colons like `dark:bg-red-500`. |
| **Screen flashes white (FOUC) on page reload** | The head script runs too late. | Verify `add_action('wp_head', ..., 0)` uses priority 0 in PHP to output early in `<head>`. |

---

# E2E Test Workflow: Ska Logic Engine MySQL Storage (Milestone 1)

> [!NOTE]
> This document outlines the testing workflow for the MySQL Flat Table storage of Ska Logic Engine, covering automatic table schema creation, deletion protection, lazy-loading optimizations, and Hybrid Routing in Dev Mode.

## Objectives
1. **MySQL Flat Table Storage:** Workflows are persisted in the `wp_ska_data_sys_workflows` flat database table instead of `wp_options`.
2. **Site Management Protection:** Confirm the workflow table sits in the `Site Management` (`ska_system`) workspace and is protected from removal.
3. **Hybrid Routing & Dev Mode:** Validate admin route registration optimization: using options cache (0ms runtime overhead) on Production (Dev Mode = 0) and live database reading on Development (Dev Mode = 1).
4. **Auto-CRUD & Dynamic App ID Mapping:** Verify auto-generated CRUD flows resolve and register under their destination tables' respective workspaces.

---

## 🧪 Test Cases

### Test Case 1: Auto-Initialization & Protected Flat Table Registration
**Status:** `[X] Pending Test`

**Steps:**
1. Upgrade Ska Logic Engine plugin to version `1.1.0`.
2. Inspect the database using phpMyAdmin or terminal to verify the existence of `wp_ska_data_sys_workflows`.
3. Verify that the obsolete option `ska_logic_simple_workflows` is pruned from the `wp_options` table.
4. Navigate to **Ska Data Pro** -> **Site Management** (or query option `ska_data_dictionary`). Verify that the "Workflows" table exists.
5. Attempt to drop the `wp_ska_data_sys_workflows` table from Ska Data Pro UI or call `drop_table()` programmatically.

**Expected Results:**
- The flat MySQL table `wp_ska_data_sys_workflows` exists with proper schemas (including `JSON` type for `graph`).
- Dropping/deleting `wp_ska_data_sys_workflows` is blocked with a security exception message: `Security: Deleting tables belonging to the Core System is not allowed.`

---

### Test Case 2: CRUD Workflow & Graph Saving in Builder UI
**Status:** `[x] DONE`

**Steps:**
1. Go to WP Admin -> Ska Logic Engine.
2. In the header, type a new Workflow ID (e.g., `test_flow_lead`) and click **Initialize empty Stream**.
3. In the Builder canvas, insert nodes (e.g., Trigger, DB Action, Client Response) and link them.
4. Click **Save Graph** in the toolbar.
5. Inspect the database record for `test_flow_lead` in `wp_ska_data_sys_workflows`.
6. Return to the Manager UI list view, click Rename to update to `test_flow_lead_updated`.
7. Click Delete to remove the workflow.

**Expected Results:**
- All CRUD modifications write immediately to MySQL.
- Saving graph updates the `graph` column JSON structure and updates `node_count` with the exact node count (e.g., 3).
- Manager UI reflects correct node count values in the columns.

---

### Test Case 3: Auto-Generated CRUD Workflow & Workspace Synchronization
**Status:** `[x] DONE`

**Steps:**
1. Ensure a custom business table exists (e.g., `leads` or `orders`) assigned to a custom App (e.g., `app_marketing`).
2. Dispatch a REST API request (or submit a form) containing a form ID like `insert_leads` or `delete_leads` where no workflow is registered yet.
3. Access WP Admin -> Ska Logic Engine.
4. Check if the `insert_leads` workflow has been created automatically and note its Workspace.

**Expected Results:**
- The engine detects the missing workflow and creates a default skeleton flow (Trigger -> DB Action -> Client Response).
- The newly created workflow is saved to the database with `app_id` auto-resolved to `app_marketing` based on the custom table's dictionary settings.

---

### Test Case 4: Hybrid Routing & Performance Optimization
**Status:** `[x] DONE`

**Steps:**
1. **Testing under Dev Mode:**
   - Set the dev mode flag `ska_system_dev_mode` to `1`.
   - Monitor database queries (e.g., using Query Monitor) to verify the route registrar queries the live table.
2. **Testing under Production Mode:**
   - Set `ska_system_dev_mode` to `0`.
   - Update a workflow to force-rebuild the cache option `ska_logic_workflow_ids`.
   - Verify that option exists in `wp_options`.
   - Access non-related admin pages. Verify zero SQL queries are made to `wp_ska_data_sys_workflows`.

**Expected Results:**
- Route registration functions perfectly in both settings with no 404/Permission errors.
- Disabling dev mode preserves admin dashboard query performance.

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Table not found in database** | `dbDelta` failed due to syntax mismatch or DB compatibility. | Verify `ska_logic_db_version` is set to `1.1.0` in `wp_options`. Delete the option to force table re-creation. |
| **Workflows table missing from Site Management** | `ska_data_dictionary` got overwritten or corrupted. | The table checks dictionary registration during plugin initialization. Re-save database settings or reactivate the plugin. |
| **Saving graph triggers error** | Invalid JSON format sent from Frontend. | Verify `ska_linear_graph` POST parameter contains valid JSON payload in DevTools Network tab. |

---

# E2E Test Workflow: System Table Schema Protection (Approach A)

> [!NOTE]
> This document details the testing steps to verify System Table Schema Protection (Approach A). The goal is to ensure that schema alterations (adding, modifying, or dropping columns/tables) on registered system tables (e.g., `wp_ska_data_sys_workflows`) are blocked at both frontend and backend levels.

## Objectives
1. **Frontend Isolation:** Verify that the schema editing elements are hidden and locked when viewing a protected table in the admin area.
2. **Backend Enforcement:** Verify that database alterations triggered via backend APIs are intercepted and blocked by the `Database_Engine` with secure error responses.

---

## 🧪 Test Cases

### Test Case 1: UI Lock Visualization (Grid & Sidebar)
**Status:** `[x] DONE`

**Steps:**
1. Log in to WP Admin and navigate to **Ska Data Pro** -> **Site Management**.
2. Select the **Workflows** table (system table `wp_ska_data_sys_workflows`).
3. Examine the rightmost column header (the Add Field location).
4. Hover over existing column headers (like `workflow_id`, `name`, `graph`).
5. Check the sidebar item for the **Workflows** table and hover over it.
6. Open a standard custom table (e.g., `leads` or `courses`).

**Expected Results:**
- On the **Workflows** table, the `[+]` button is replaced by a lock icon with the tooltip: "Core system tables are locked".
- On the **Workflows** table, column header edits are locked: no pencil dropdown edit triggers appear.
- On the **Workflows** table sidebar item, the kebab settings icon is completely hidden, preventing rename or drop modals from opening.
- Standard custom tables (like `leads` or `courses`) retain fully functional `[+]` buttons, pencil header triggers, and sidebar kebab settings dropdown.

---

### Test Case 2: Backend Alteration Blockade (Database Engine Checks)
**Status:** `[X] DONE`

**Steps:**
1. Open the browser DevTools Console on the admin page.
2. Construct and dispatch an AJAX request calling `ska_data_add_column` targeting the `wp_ska_data_sys_workflows` table.
3. Construct and dispatch another request calling `ska_data_drop_column` targeting `wp_ska_data_sys_workflows`.
4. Attempt to run custom code calling `Database_Engine::get_instance()->drop_table('wp_ska_data_sys_workflows')` or renaming the table.

**Expected Results:**
- All alteration AJAX actions return a JSON failure response containing: `Security: Modifying the schema of tables belonging to the Core System is not allowed.`
- Table deletion attempts return `Security: Deleting tables belonging to the Core System is not allowed.`
- Table rename attempts return `Security: Modification of Core System board configuration is not allowed.`

---

## 🛠 Troubleshooting Guide

| Symptom | Potential Cause | Remedial Action |
| :--- | :--- | :--- |
| **Kebab settings trigger still visible in sidebar for workflows** | Table is not registered in the `ska_data_protected_tables` array. | Ensure the prefix is correct and the filter hook in `Ska_Logic_Core::protect_system_tables()` is correctly returning the full table name. |
| **Pencil edit icons or add button visible in grid** | Current table `$is_protected` logic failed to evaluate. | Check `Database_Engine::get_instance()->is_table_protected($current_table)` to ensure it returns true for `wp_ska_data_sys_workflows`. |
| **Backend actions bypass protection checks** | Database Engine methods are called bypassing the `is_table_protected()` checker. | Confirm that `add_column()`, `update_column()`, `drop_column()`, `drop_table()`, and `rename_custom_table()` in `class-database-engine.php` explicitly invoke `is_table_protected()`. |
