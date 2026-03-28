# SKA NO-CODE ECOSYSTEM - MASTER PLAN (AI-READABLE)
@version: 1.2.0 | @stack: WP-Core, Tailwind JIT, SCF, Next.js | @focus: Performance-first

## 1. DIRECTORY ARCHITECTURE
```text
ska-ecosystem/
├── .ska-ai/ (BRAIN)        -> .cursorrules, system_map, memory/, modules-docs/
├── wp-plugins/
│   ├── ska-builder-core/   -> [DESIGN | DATA | LOGIC | VISIBILITY | UI-EDITOR]
│   │   └── blocks/         -> Atomic Blocks (Ska-Box, Ska-Loop, etc.)
│   └── ska-bridge/         -> [ADAPTER] Import (html2tailwind / AI-Refactor) & Export (JSON-Gen) | (Paid Module)
└── ska-nextjs/             -> React Components (1:1 Map) + API Fetcher
```

## 2. CORE ARCHITECTURE (MINDMAP SUMMARY)
- **Block Manager:** JSON Schema Definition + Dynamic Registration.
- **Editor Engine:** Gutenberg HOC + Tailwind Class Injector + Visual Drag-Drop.
- **Data Engine:** SCF/WC Connector + Dynamic Placeholders (`{{tag}}`) + Loop Context Aware.
- **Design Engine:** Local PHP JIT Compiler (Regex-based) + Hybrid Fallback (CDN).
- **Logic Engine:** Conditional Rendering (`{{#if}}`) + Iteration (`{{#foreach}}`).
- **Expansion:** JSON Export -> React Mapping -> Optimized REST API.

## 3. DEVELOPMENT ROADMAP
- **Phase 1 (MVP - COMPLETED):** Ska Builder Core, Style Manager, Atomic Blocks, Basic SCF Binding, Logic Engine v1 (If/Foreach), Local JIT Compiler.
- **Phase 2 (Optimization):** Advanced JIT Rules (Typography, Layout), Nested Loops Optimization, Component Drip.
- **Phase 3 (Ecosystem):** JSON Bridge, Next.js Mapping, AI-assisted Design Migration.

## 4. CONVERSION STRATEGY (MANUAL VS. AI)
| Type | Scope | Method |
| :--- | :--- | :--- |
| **Deterministic** | Ska Blocks, SCF Data, Design Tokens, Routing | Manual Code (Fast, 100% Stable) |
| **Probabilistic** | 3rd Party HTML/CSS, Legacy Code, Complex Logic | AI Intervention (Refactor to Tailwind) |

## 5. TECHNICAL CONSTRAINTS & PRD
- **Prefix:** Use standard Tailwind classes. Avoid custom prefixes.
- **Data Integrity:** JSON Output must be "Clean" (No WP-specific junk).
- **Performance:** 90+ PageSpeed score. Modular loading only.
- **Nested Loops:** Key differentiator. Must handle Parent-Child ID context passing.
- **Scalability:** Support 1000s of records (LMS/Booking focus).

## 6. MODULE REGISTRY (STATUS)
- **Design Engine:** 🟢 Implemented (Alpha) | Local JIT & Hybrid CDN.
- **Data Engine:** 🟢 Implemented (Beta) | SCF/WC Binding.
- **Atomic Blocks:** 🟢 Implemented | Core layout & text blocks.
- **Logic Engine:** 🟢 Implemented (v1) | `{{#if}}` & `{{#foreach}}` tags.
- **Bridge System:** 🟢 Implemented (v1) | html2tailwind (Import) & Next.js Mapping (Export).

**AI Instruction:** Use this file as the primary source for Project Scope. Refer to `.ska-ai/modules-docs/` for specific implementation details of each component.