# Graph Report - theme-elementary  (2026-06-24)

## Corpus Check
- 89 files · ~41,751 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 754 nodes · 872 edges · 87 communities (65 shown, 22 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 14 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `60ca7692`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- [[_COMMUNITY_Community 0|Community 0]]
- [[_COMMUNITY_Community 1|Community 1]]
- [[_COMMUNITY_Community 2|Community 2]]
- [[_COMMUNITY_Community 3|Community 3]]
- [[_COMMUNITY_Community 4|Community 4]]
- [[_COMMUNITY_Community 5|Community 5]]
- [[_COMMUNITY_Community 6|Community 6]]
- [[_COMMUNITY_Community 7|Community 7]]
- [[_COMMUNITY_Community 8|Community 8]]
- [[_COMMUNITY_Community 9|Community 9]]
- [[_COMMUNITY_Community 10|Community 10]]
- [[_COMMUNITY_Community 11|Community 11]]
- [[_COMMUNITY_Community 12|Community 12]]
- [[_COMMUNITY_Community 13|Community 13]]
- [[_COMMUNITY_Community 14|Community 14]]
- [[_COMMUNITY_Community 15|Community 15]]
- [[_COMMUNITY_Community 16|Community 16]]
- [[_COMMUNITY_Community 17|Community 17]]
- [[_COMMUNITY_Community 18|Community 18]]
- [[_COMMUNITY_Community 19|Community 19]]
- [[_COMMUNITY_Community 20|Community 20]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]
- [[_COMMUNITY_Community 24|Community 24]]
- [[_COMMUNITY_Community 25|Community 25]]
- [[_COMMUNITY_Community 26|Community 26]]
- [[_COMMUNITY_Community 27|Community 27]]
- [[_COMMUNITY_Community 28|Community 28]]
- [[_COMMUNITY_Community 29|Community 29]]
- [[_COMMUNITY_Community 30|Community 30]]
- [[_COMMUNITY_Community 31|Community 31]]
- [[_COMMUNITY_Community 32|Community 32]]
- [[_COMMUNITY_Community 33|Community 33]]
- [[_COMMUNITY_Community 34|Community 34]]
- [[_COMMUNITY_Community 35|Community 35]]
- [[_COMMUNITY_Community 36|Community 36]]
- [[_COMMUNITY_Community 37|Community 37]]
- [[_COMMUNITY_Community 38|Community 38]]
- [[_COMMUNITY_Community 39|Community 39]]
- [[_COMMUNITY_Community 40|Community 40]]
- [[_COMMUNITY_Community 41|Community 41]]
- [[_COMMUNITY_Community 43|Community 43]]
- [[_COMMUNITY_Community 44|Community 44]]
- [[_COMMUNITY_Community 45|Community 45]]
- [[_COMMUNITY_Community 46|Community 46]]
- [[_COMMUNITY_Community 47|Community 47]]
- [[_COMMUNITY_Community 48|Community 48]]
- [[_COMMUNITY_Community 49|Community 49]]
- [[_COMMUNITY_Community 50|Community 50]]
- [[_COMMUNITY_Community 51|Community 51]]
- [[_COMMUNITY_Community 52|Community 52]]
- [[_COMMUNITY_Community 53|Community 53]]
- [[_COMMUNITY_Community 54|Community 54]]
- [[_COMMUNITY_Community 56|Community 56]]
- [[_COMMUNITY_Community 58|Community 58]]
- [[_COMMUNITY_Community 59|Community 59]]
- [[_COMMUNITY_Community 60|Community 60]]
- [[_COMMUNITY_Community 61|Community 61]]
- [[_COMMUNITY_Community 62|Community 62]]
- [[_COMMUNITY_Community 63|Community 63]]
- [[_COMMUNITY_Community 79|Community 79]]
- [[_COMMUNITY_Community 80|Community 80]]
- [[_COMMUNITY_Community 81|Community 81]]
- [[_COMMUNITY_Community 82|Community 82]]
- [[_COMMUNITY_Community 83|Community 83]]
- [[_COMMUNITY_Community 84|Community 84]]
- [[_COMMUNITY_Community 85|Community 85]]
- [[_COMMUNITY_Community 86|Community 86]]

## God Nodes (most connected - your core abstractions)
1. `scripts` - 30 edges
2. `Assets` - 23 edges
3. `TestCase` - 22 edges
4. `Util` - 19 edges
5. `require-dev` - 15 edges
6. `fontSize` - 13 edges
7. `Main` - 12 edges
8. `Steps` - 12 edges
9. `ThemeOptions` - 11 edges
10. `AssetsHmrTest` - 11 edges

## Surprising Connections (you probably didn't know these)
- `AssetsTailwindTest` --references--> `Assets`  [EXTRACTED]
  tests/php/inc/Core/AssetsTailwindTest.php → inc/Core/Assets.php
- `AssetsTest` --references--> `Assets`  [EXTRACTED]
  tests/php/inc/Core/AssetsTest.php → inc/Core/Assets.php
- `MenuTest` --references--> `Menu`  [EXTRACTED]
  tests/php/inc/Core/MenuTest.php → inc/Core/Menu.php
- `TemplatesTest` --references--> `Templates`  [EXTRACTED]
  tests/php/inc/Core/TemplatesTest.php → inc/Core/Templates.php
- `ThemeSetupTest` --references--> `ThemeSetup`  [EXTRACTED]
  tests/php/inc/Core/ThemeSetupTest.php → inc/Core/ThemeSetup.php

## Import Cycles
- 1-file cycle: `webpack.config.js -> webpack.config.js`

## Communities (87 total, 22 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.21
Nodes (13): typography, h1, h2, h4, h5, h6, typography, typography (+5 more)

### Community 1 - "Community 1"
Cohesion: 0.05
Nodes (42): dealerdirect/phpcodesniffer-composer-installer, phpstan/extension-installer, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+34 more)

### Community 2 - "Community 2"
Cohesion: 0.06
Nodes (30): ASSETS_BUILD_DIR, BROWSER_SYNC_FILES, bsPort, COMPONENTS_DIR, componentScripts, componentStyles, configs, CONTEXT_DIRS (+22 more)

### Community 3 - "Community 3"
Cohesion: 0.14
Nodes (14): 1. Detect mode, 2. Setup: offer the pilot bootstrap, 3. Preconditions (verify; do not silently fix), 4. Gather inputs, 5. Confirm, 6. Run init (with consent), 7. After init, Be interactive (required for Copilot) (+6 more)

### Community 4 - "Community 4"
Cohesion: 0.07
Nodes (30): scripts, build:assets, build:assets:dev, build:blocks, build:blocks:dev, build:dev, build:prod, init (+22 more)

### Community 5 - "Community 5"
Cohesion: 0.07
Nodes (28): devDependencies, @babel/core, browser-sync, browser-sync-webpack-plugin, browserslist, copy-webpack-plugin, css-minimizer-webpack-plugin, dotenv (+20 more)

### Community 6 - "Community 6"
Cohesion: 0.09
Nodes (22): palette, spacing, contentSize, wideSize, settings, appearanceTools, color, custom (+14 more)

### Community 7 - "Community 7"
Cohesion: 0.18
Nodes (11): Adding a new class, Architecture overview, Conditional registration, Development Guide, Directory layout, Helpers, Notes, Picking a base (+3 more)

### Community 8 - "Community 8"
Cohesion: 0.29
Nodes (6): Architecture, Flag on review (priority order), Mandatory, PHP rules: framework, WordPress, security, tests, WordPress conventions, WordPress security (always)

### Community 9 - "Community 9"
Cohesion: 0.10
Nodes (20): 0. Plan and announce - before any other work, 1. Discover, 2. Introspect once per session (cache result), 3. Canonical layout, 4. Derive test cases from the developer brief - BEFORE any scaffold call, 5. Apply conventions, invoke the engine, 6. Process the result, 6a. Adaptive wiring (+12 more)

### Community 10 - "Community 10"
Cohesion: 0.10
Nodes (20): 0. Parse the request, 1. Detect what already exists, 2. Build the scaffold plan, 3. Confirm the full plan before doing anything, 4. Execute Phase A, 5. Execute Phase B, 6. Consolidated final report, Error handling (+12 more)

### Community 11 - "Community 11"
Cohesion: 0.07
Nodes (8): Assets, AssetsHmrTest, AssetsTailwindTest, AssetsTest, MainTest, TestCase, WP_Styles, WP_UnitTestCase

### Community 12 - "Community 12"
Cohesion: 0.11
Nodes (18): 1. Detect mode, 2. Setup: offer the pilot bootstrap, 3. Preconditions (verify; do not silently fix), 4. Gather inputs, 5. Confirm, 6. Run init (with consent), 7. After init, 8. Implement the brief by handing off to scaffold (setup only, when features were described) (+10 more)

### Community 13 - "Community 13"
Cohesion: 0.11
Nodes (18): 1. Discover, 2. Introspect (once per session), 3. Canonical layout, 4. Derive test cases (BEFORE any scaffold call), 5. Invoke the engine (never hand-write what it covers), 6. Process the result, 7. TDD loop (mandatory), 7a. PHP compliance (required, on changed PHP) (+10 more)

### Community 14 - "Community 14"
Cohesion: 0.21
Nodes (15): color, radius, :active, border, color, :focus, :hover, :visited (+7 more)

### Community 15 - "Community 15"
Cohesion: 0.18
Nodes (6): AssetLoader, ComponentLoader, Components, Logger, FrameworkLogger, Shareable

### Community 16 - "Community 16"
Cohesion: 0.13
Nodes (14): Advanced, Block dev server port, Configuration, Disabling the BrowserSync client only, Enabling / disabling HMR, How It Works, HTTPS, Known Limitations (+6 more)

### Community 18 - "Community 18"
Cohesion: 0.19
Nodes (3): Templates, TemplatesTest, TemplateLoader

### Community 19 - "Community 19"
Cohesion: 0.06
Nodes (8): MediaTextInteractive, Menu, MenuTest, ThemeSetup, ThemeSetupTest, Registrable, AuthorBio, AuthorBioTest

### Community 21 - "Community 21"
Cohesion: 0.17
Nodes (11): Adding a New Module, Adding a New Script, Adding New Scripts or Modules, Asset Building Process, Avoid Bundling Specific Files, Directory Structure, How the Asset Building Works, How to Exclude Files (+3 more)

### Community 22 - "Community 22"
Cohesion: 0.25
Nodes (8): core/navigation, core/query-pagination, core/quote, core/separator, typography, border, color, blocks

### Community 23 - "Community 23"
Cohesion: 0.22
Nodes (9): core/pullquote, width, border, spacing, bottom, left, right, top (+1 more)

### Community 24 - "Community 24"
Cohesion: 0.47
Nodes (9): err(), install_pip(), install_pipx(), install_uv(), log(), resolve_method(), warn(), have() (+1 more)

### Community 25 - "Community 25"
Cohesion: 0.19
Nodes (13): core/heading, core/post-navigation-link, core/site-title, typography, typography, typography, typography, typography (+5 more)

### Community 27 - "Community 27"
Cohesion: 0.22
Nodes (8): After installing, Files, graphify install scripts, macOS / Linux, Manual install (no scripts), Quick start, What the installer does, Windows (PowerShell)

### Community 28 - "Community 28"
Cohesion: 0.38
Nodes (3): Main, Loader, Singleton

### Community 29 - "Community 29"
Cohesion: 0.33
Nodes (6): core/post-comments, elements, spacing, typography, h3, typography

### Community 30 - "Community 30"
Cohesion: 0.25
Nodes (7): { accessSync, constants }, args, { argv }, { join }, phpcbfProcess, scriptPath, { spawn }

### Community 31 - "Community 31"
Cohesion: 0.15
Nodes (13): 1. Initialize, 1. Set up the theme, 2. Add a feature later, 2. Add the features, AI skills vs raw CLI, At a glance, Code consistency and standards, Command reference (+5 more)

### Community 32 - "Community 32"
Cohesion: 0.15
Nodes (12): author, bugs, url, dependencies, @wordpress/interactivity, description, homepage, keywords (+4 more)

### Community 34 - "Community 34"
Cohesion: 0.29
Nodes (6): Checklist, Description, Fixes/Covers issue, Screenshots, Technical Details, To-do

### Community 35 - "Community 35"
Cohesion: 0.25
Nodes (4): argv, config, { execFileSync }, path

### Community 37 - "Community 37"
Cohesion: 0.40
Nodes (5): Commands, Copilot instructions — Theme Elementary, Review conduct, Stack, Universal rules

### Community 38 - "Community 38"
Cohesion: 0.60
Nodes (5): have(), log(), ok(), warn(), verify.sh script

### Community 39 - "Community 39"
Cohesion: 0.33
Nodes (5): fs, { getComponentEntries }, os, path, getComponentEntries()

### Community 40 - "Community 40"
Cohesion: 0.40
Nodes (4): Additional Information, Description, Screenshots, Steps to Reproduce

### Community 43 - "Community 43"
Cohesion: 0.83
Nodes (3): have(), log(), uninstall.sh script

### Community 45 - "Community 45"
Cohesion: 0.50
Nodes (3): References, Summary, Tasks

### Community 46 - "Community 46"
Cohesion: 0.50
Nodes (3): Acceptance Criteria, References, Summary

### Community 47 - "Community 47"
Cohesion: 0.50
Nodes (3): extends, ignoreFiles, rules

### Community 80 - "Community 80"
Cohesion: 0.25
Nodes (8): AI tooling :robot:, Development :computer:, Does this interest you?, Get Started :rocket:, Method 1 (Recommended), Method 2, Theme Elementary, Understand the Folder Structure :open_file_folder:

### Community 81 - "Community 81"
Cohesion: 0.25
Nodes (7): customTemplates, $schema, blockGap, styles, spacing, templateParts, version

### Community 82 - "Community 82"
Cohesion: 0.29
Nodes (7): AGENTS.md — Theme Elementary, AI tooling, Authoritative rules, Guardrails (all AI tools) - BASE, non-negotiable, Key principles (full detail in the files above), Knowledge graph (graphify), Structure

### Community 83 - "Community 83"
Cohesion: 0.33
Nodes (6): Install (one time), Knowledge graph (Graphify), Optional: semantic layer, Query (this is what saves tokens), Regenerate, What is tracked

### Community 84 - "Community 84"
Cohesion: 0.40
Nodes (5): Internal testing (v2), Notes, Prerequisites, Steps, What to verify

### Community 85 - "Community 85"
Cohesion: 0.50
Nodes (4): overrides, minimatch, serialize-javascript, webpack-dev-server

### Community 86 - "Community 86"
Cohesion: 0.67
Nodes (3): repository, type, url

## Knowledge Gaps
- **357 isolated node(s):** `args`, `modifiedFiles`, `extends`, `ignoreFiles`, `rules` (+352 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **22 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `TestCase` connect `Community 11` to `Community 36`, `Community 15`, `Community 17`, `Community 18`, `Community 19`, `Community 26`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **Why does `scaffold` connect `Community 9` to `Community 79`?**
  _High betweenness centrality (0.010) - this node is a cross-community bridge._
- **Are the 6 inferred relationships involving `Util` (e.g. with `.test_decrypt_returns_false_for_tampered_value()` and `.test_encrypt_decrypt_roundtrips_with_key_constant()`) actually correct?**
  _`Util` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `args`, `modifiedFiles`, `extends` to the rest of the system?**
  _357 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Community 1` be split into smaller, more focused modules?**
  _Cohesion score 0.046511627906976744 - nodes in this community are weakly interconnected._
- **Should `Community 2` be split into smaller, more focused modules?**
  _Cohesion score 0.05714285714285714 - nodes in this community are weakly interconnected._
- **Should `Community 3` be split into smaller, more focused modules?**
  _Cohesion score 0.14285714285714285 - nodes in this community are weakly interconnected._