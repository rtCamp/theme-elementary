# Graph Report - theme-elementary  (2026-10-01)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 999 nodes · 1391 edges · 83 communities (47 shown, 36 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 37 edges (avg confidence: 0.93)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- fontSize
- composer.json
- webpack.config.js
- ThemeOptions
- scripts
- devDependencies
- settings
- AuthorBioTest
- init
- MediaTextInteractive
- Workflow
- Assets
- Util
- dev-tools.js
- button
- FeatureRegistry
- Live reload and block refresh
- TestCase
- rtCamp\WPPrimitives\Contracts\Interfaces\Shareable
- Steps
- install.sh script
- Asset Building Process
- blocks
- padding
- Encryption
- typography
- AssetsHmrTest
- Templates
- Main
- core/post-comments
- phpcbf.js
- FeaturesSettingsPage
- package.json
- README.md
- PULL_REQUEST_TEMPLATE.md
- dev-tools.test.js
- FeaturesTest
- Copilot instructions — Theme Elementary
- Maintain the starter theme
- Initialize and manage the theme
- graphify install scripts
- webpack.blocks.config.js
- Getting started
- LoggerTest
- ThemeSetup
- Local development
- Add a feature with CLI or AI
- .stylelintrc.json
- @wordpress/interactivity
- typography
- bug.md
- epic.md
- task.md
- babel.config.js
- @rtcamp/eslint-config
- CleanBuildPlugin
- CssAssetMetadataPlugin
- CssAssetRtlPlugin
- uninstall.sh
- getPluginName
- bugs
- dependencies
- browser-sync-webpack-plugin
- shared/README.md
- overrides
- repository

## God Nodes (most connected - your core abstractions)
1. `Main` - 42 edges
2. `TestCase` - 40 edges
3. `Util` - 35 edges
4. `Assets` - 30 edges
5. `scripts` - 30 edges
6. `AssetsEnqueueTest` - 19 edges
7. `ThemeOptions` - 18 edges
8. `ThemeOptionsTest` - 17 edges
9. `FeatureRegistry` - 16 edges
10. `MediaTextInteractive` - 14 edges

## Surprising Connections (you probably didn't know these)
- `Adding a new class` --references--> `TestCase`  [INFERRED]
  DEVELOPMENT.md → tests/php/TestCase.php
- `Mandatory` --references--> `TestCase`  [INFERRED]
  .github/instructions/primitives-php.instructions.md → tests/php/TestCase.php
- `Theme infrastructure` --references--> `Logger`  [INFERRED]
  docs/features.md → inc/Core/Logger.php
- `Key principles (full detail in the files above)` --references--> `Main`  [INFERRED]
  AGENTS.md → inc/Main.php
- `Structure` --references--> `Assets`  [INFERRED]
  AGENTS.md → inc/Core/Assets.php

## Import Cycles
- None detected.

## Communities (83 total, 36 thin omitted)

### Community 0 - "fontSize"
Cohesion: 0.21
Nodes (13): typography, h1, h2, h4, h5, h6, typography, typography (+5 more)

### Community 1 - "composer.json"
Cohesion: 0.04
Nodes (44): dealerdirect/phpcodesniffer-composer-installer, phpstan/extension-installer, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+36 more)

### Community 2 - "webpack.config.js"
Cohesion: 0.06
Nodes (32): ASSETS_BUILD_DIR, BROWSER_SYNC_FILES, bsPort, COMPONENTS_DIR, componentScripts, componentStyles, configs, CONTEXT_DIRS (+24 more)

### Community 3 - "ThemeOptions"
Cohesion: 0.05
Nodes (23): 0. Plan and announce - before any other work, 1. Discover, 2. Introspect once per session (cache result), 3. Canonical layout, 4. Derive test cases from the developer brief - BEFORE any scaffold call, 5. Apply conventions, invoke the engine, 6. Process the result, 6a. Adaptive wiring (+15 more)

### Community 4 - "scripts"
Cohesion: 0.07
Nodes (30): scripts, build:assets, build:assets:dev, build:blocks, build:blocks:dev, build:dev, build:prod, init (+22 more)

### Community 5 - "devDependencies"
Cohesion: 0.07
Nodes (30): devDependencies, @babel/core, browser-sync, browser-sync-webpack-plugin, browserslist, copy-webpack-plugin, css-minimizer-webpack-plugin, dotenv (+22 more)

### Community 6 - "settings"
Cohesion: 0.07
Nodes (26): palette, spacing, customTemplates, contentSize, wideSize, $schema, settings, appearanceTools (+18 more)

### Community 7 - "AuthorBioTest"
Cohesion: 0.11
Nodes (11): Adapt an included example, Adding a new class, Before adding code, Conditional registration, Development guide, Picking a base, Post type, Register a post type and taxonomy (+3 more)

### Community 8 - "init"
Cohesion: 0.09
Nodes (22): 1. Detect mode, 2. Install dependencies, 3. Preconditions (verify; do not silently fix), 4. Gather inputs, 5. Confirm, 6. Run init (with consent), 7. After init, 8. Implement the brief by handing off to scaffold (setup only, when features were described) (+14 more)

### Community 10 - "Workflow"
Cohesion: 0.10
Nodes (20): 0. Parse the request, 1. Detect what already exists, 2. Build the scaffold plan, 3. Confirm the full plan before doing anything, 4. Execute Phase A, 5. Execute Phase B, 6. Consolidated final report, Error handling (+12 more)

### Community 11 - "Assets"
Cohesion: 0.06
Nodes (4): Assets, AssetsEnqueueTest, AssetsTailwindTest, AssetsTest

### Community 13 - "dev-tools.js"
Cohesion: 0.22
Nodes (18): addComposerEntry(), basePlugins(), containerRoot(), deleteIfEmpty(), detect(), developerPlugins(), ensureGitignored(), onDisable() (+10 more)

### Community 14 - "button"
Cohesion: 0.18
Nodes (16): color, core/separator, radius, :active, border, color, :focus, :hover (+8 more)

### Community 15 - "FeatureRegistry"
Cohesion: 0.15
Nodes (4): 2. Introspect (once per session), AbstractThemeFeature, FeatureRegistry, AuthorBio

### Community 16 - "Live reload and block refresh"
Cohesion: 0.12
Nodes (17): Advanced, Block dev server port, Blocks (`start:blocks` + Fast Refresh), Configuration, Disabling the BrowserSync client only, Enabling / disabling HMR, How It Works, HTTPS (+9 more)

### Community 19 - "Steps"
Cohesion: 0.12
Nodes (16): 1. Discover, 3. Canonical layout, 5. Invoke the engine (never hand-write what it covers), 6. Process the result, 7. TDD loop (mandatory), 7a. PHP compliance (required, on changed PHP), 8. Escalate when stuck (do not guess), 9a. Refresh the knowledge graph (+8 more)

### Community 20 - "install.sh script"
Cohesion: 0.27
Nodes (14): err(), install_pip(), install_pipx(), install_uv(), log(), resolve_method(), install.sh script, have() (+6 more)

### Community 21 - "Asset Building Process"
Cohesion: 0.18
Nodes (12): Adding a New Module, Adding a New Script, Adding New Scripts or Modules, Asset Building Process, Avoid Bundling Specific Files, Components and blocks, Directory Structure, How to Exclude Files (+4 more)

### Community 22 - "blocks"
Cohesion: 0.22
Nodes (10): core/navigation, core/pullquote, core/query-pagination, core/quote, width, typography, border, spacing (+2 more)

### Community 23 - "padding"
Cohesion: 0.22
Nodes (9): bottom, left, right, top, blockGap, padding, styles, color (+1 more)

### Community 25 - "typography"
Cohesion: 0.38
Nodes (7): core/heading, typography, typography, typography, fontFamily, fontWeight, lineHeight

### Community 28 - "Main"
Cohesion: 0.13
Nodes (8): Architecture, Flag on review (priority order), Mandatory, PHP rules: framework, WordPress, security, tests, WordPress conventions, WordPress security (always), Main, MainTest

### Community 29 - "core/post-comments"
Cohesion: 0.33
Nodes (6): core/post-comments, elements, spacing, typography, h3, typography

### Community 30 - "phpcbf.js"
Cohesion: 0.17
Nodes (7): { accessSync, constants }, args, { argv }, { join }, phpcbfProcess, scriptPath, { spawn }

### Community 32 - "package.json"
Cohesion: 0.06
Nodes (32): author, description, homepage, @rtcamp/stylelint-config, keywords, license, name, private (+24 more)

### Community 33 - "README.md"
Cohesion: 0.07
Nodes (32): Knowledge graph (Graphify), AI skills, Safety, Before you open a PR, Building assets, Contributing to Theme Elementary, Development setup, License (+24 more)

### Community 34 - "PULL_REQUEST_TEMPLATE.md"
Cohesion: 0.29
Nodes (6): Checklist, Description, Fixes/Covers issue, Screenshots, Technical Details, To-do

### Community 35 - "dev-tools.test.js"
Cohesion: 0.05
Nodes (33): AGENTS.md — Theme Elementary, AI tooling, Authoritative rules, Guardrails (all AI tools) - BASE, non-negotiable, Key principles (full detail in the files above), Knowledge graph (graphify), Structure, argv (+25 more)

### Community 37 - "Copilot instructions — Theme Elementary"
Cohesion: 0.33
Nodes (5): Commands, Copilot instructions — Theme Elementary, Review conduct, Stack, Universal rules

### Community 38 - "Maintain the starter theme"
Cohesion: 0.20
Nodes (10): Checks, Cleanup checks, Documentation publishing, Knowledge graph, Known gaps, Local dependency development, Maintain the starter theme, Record validation results (+2 more)

### Community 39 - "Initialize and manage the theme"
Cohesion: 0.22
Nodes (9): Before init, Change things later, CLI or AI, Initialize and manage the theme, Optional dependencies, Review the result, Troubleshooting, What changes (+1 more)

### Community 40 - "graphify install scripts"
Cohesion: 0.22
Nodes (8): After installing, Files, graphify install scripts, macOS / Linux, Manual install (no scripts), Quick start, What the installer does, Windows (PowerShell)

### Community 41 - "webpack.blocks.config.js"
Cohesion: 0.33
Nodes (3): @wordpress/scripts, config, devServerPort

### Community 42 - "Getting started"
Cohesion: 0.25
Nodes (8): 1. Get the starter theme, 2. Install dependencies, 3. Personalize, 4. Start WordPress and activate the theme, 5. Make a visible edit, Getting started, Prerequisites, Standalone `wp-env` route

### Community 44 - "ThemeSetup"
Cohesion: 0.06
Nodes (8): Flag, Theme structure, Autoloader, Menu, ThemeSetup, AutoloaderTest, MenuTest, ThemeSetupTest

### Community 45 - "Local development"
Cohesion: 0.29
Nodes (7): Automatic frontend reload, Build for delivery, Check a change, Edit source and see the result, Local development, Start and stop WordPress, Troubleshooting

### Community 46 - "Add a feature with CLI or AI"
Cohesion: 0.29
Nodes (7): Add a feature with CLI or AI, AI route, Before generating, CLI route, Code consistency and standards, Troubleshooting, Verify and continue

### Community 47 - ".stylelintrc.json"
Cohesion: 0.33
Nodes (5): extends, ignoreFiles, @rtcamp/stylelint-config, rules, scss/at-rule-no-unknown

### Community 48 - "@wordpress/interactivity"
Cohesion: 0.47
Nodes (3): @wordpress/interactivity, play(), playVideo()

### Community 49 - "typography"
Cohesion: 0.33
Nodes (6): core/post-navigation-link, core/site-title, typography, typography, typography, textTransform

### Community 50 - "bug.md"
Cohesion: 0.40
Nodes (4): Additional Information, Description, Screenshots, Steps to Reproduce

### Community 51 - "epic.md"
Cohesion: 0.50
Nodes (3): References, Summary, Tasks

### Community 52 - "task.md"
Cohesion: 0.50
Nodes (3): Acceptance Criteria, References, Summary

### Community 61 - "uninstall.sh"
Cohesion: 0.83
Nodes (3): have(), log(), uninstall.sh script

### Community 62 - "getPluginName"
Cohesion: 0.33
Nodes (6): getPluginName(), isNotOneOfPlugins(), isNotPlugin(), isPlugin(), setCssOutputPath(), withoutFastRefresh()

### Community 85 - "overrides"
Cohesion: 0.50
Nodes (4): overrides, minimatch, serialize-javascript, webpack-dev-server

### Community 86 - "repository"
Cohesion: 0.67
Nodes (3): repository, type, url

## Knowledge Gaps
- **388 isolated node(s):** `dealerdirect/phpcodesniffer-composer-installer`, `phpstan/extension-installer`, `description`, `homepage`, `license` (+383 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 538 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **36 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `AGENTS.md — Theme Elementary` connect `dev-tools.test.js` to `README.md`?**
  _High betweenness centrality (0.250) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `Main` (e.g. with `Key principles (full detail in the files above)` and `2. Introspect once per session (cache result)`) actually correct?**
  _`Main` has 6 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `TestCase` (e.g. with `Adding a new class` and `Mandatory`) actually correct?**
  _`TestCase` has 2 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `Assets` (e.g. with `Structure` and `Theme structure`) actually correct?**
  _`Assets` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `dealerdirect/phpcodesniffer-composer-installer`, `phpstan/extension-installer`, `description` to the rest of the system?**
  _388 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `composer.json` be split into smaller, more focused modules?**
  _Cohesion score 0.044444444444444446 - nodes in this community are weakly interconnected._
- **Should `webpack.config.js` be split into smaller, more focused modules?**
  _Cohesion score 0.062388591800356503 - nodes in this community are weakly interconnected._