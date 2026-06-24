# AGENTS.md — Theme Elementary

Tool-agnostic brief for AI coding agents (Claude Code, Copilot coding agent, Codex). A custom WordPress **block theme** built on `rtcamp/wp-framework` (in `vendor/`).

## Authoritative rules

The review rules ARE the coding rules: the same files Copilot reviews against. Follow them when writing code; they hold the full detail:

- `.github/instructions/framework-php.instructions.md`: framework architecture, security, testing, and the do/don't flags. Shipped from `rtcamp/wp-framework`, generated locally by `npm run sync-ai` (absent until then).
- `.github/instructions/structure.instructions.md`: theme layout and wiring.
- `.github/copilot-instructions.md`: overview + conventions.

## Key principles (full detail in the files above)

- **TDD**: write the failing PHPUnit test first (`tests/php/` mirrors `inc/`), then code.
- **Don't default to Singleton.** Hook WordPress via the framework `Loader` + `Registrable`; use `Shareable` only when an instance must be retrieved later via `get_shared()`. `Singleton` is for `Main` only.
- **Extend the framework abstracts** (e.g. an options page → `AbstractSettingsPage`) instead of hand-rolling registration.
- **Prefer `theme.json` / block patterns / template parts** over hardcoded markup or styling in PHP.
- **WordPress security**: escape/sanitize, nonce + `current_user_can()` before mutations, `$wpdb->prepare()`, a real REST `permission_callback`.
- `declare( strict_types = 1 );`, full types, `@package`/`@since`, `static::` not `self::`, PSR-4 (namespace === dir).

## Structure

`inc/` (PSR-4 `rtCamp\Theme\Elementary\`): `Main.php` (boot), `Core/` (`ThemeSetup`/`Menu`/`Assets`, implement `Registrable`), `Modules/`. `Main::CLASSES` lists classes directly (no `AbstractModule` grouping yet). Block config in `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/`. Text domain `elementary-theme`. `tests/php/` mirrors `inc/`; `inc/Modules/` classes are scaffolding; delete unused.

To add a feature: write the test, implement `Registrable` (or extend an abstract), add its `::class` to `Main::CLASSES`.

## AI tooling

The two core tasks, **init** (set up / manage the theme) and **scaffold** (add one
feature, TDD-first), exist for both assistants and must stay in step:

- **Claude Code:** skills in `.claude/skills/` (`init`, `scaffold`, `setup`). Index: `.claude/skills/README.md`.
- **GitHub Copilot:** prompt files in `.github/prompts/` (`init`, `scaffold`), invoked as `/init` and `/scaffold` in Copilot Chat.
- Both follow this file and the path-scoped rules. `scaffold` / `setup` track `@rtcamp/wp-tooling`; framework instructions re-sync with `npm run sync-ai`.

## Knowledge graph (graphify)

The repo keeps a queryable code graph in `graphify-out/` (`graph.json` + `GRAPH_REPORT.md`) covering this theme (and, optionally, the `wp-framework`/`wp-tooling` it builds on). Use it to understand the codebase, and keep it current. Tell the user each graphify step in <=30 words (50 max). Only the `init`/`scaffold` agentic tools run these commands; Copilot review does not. Setup detail: `docs/knowledge-graph.md`.

**Graph-first: do not read source files to understand them when the graph can answer.** Before opening a file to learn what a symbol does, how a subsystem works, or how the theme wires together - including `wp-tooling` engine internals (token derivation, capability removal, etc.) - query the graph (`/graphify query "<q>"`, `explain "<symbol>"`, `path "A" "B"`). You almost never need to read engine source: the engine is a black box these skills invoke, and the skill already documents the outcome (how a theme name becomes namespace/package/prefixes). Read a file only when the graph does not answer, or when you need exact code to edit.

**Before answering a question about the codebase, or before reading a file to understand code:**

1. Graph exists (`graphify-out/graph.json`)? → query it: `/graphify query "<question>"`.
2. graphify installed but no graph? → offer to build (`/graphify .`), say what + rough cost in one line, build on consent.
3. graphify not installed (`command -v graphify` fails)? → ask "install graphify? (y/n)". On yes: run `scripts/graphify/install.sh`, then `scripts/graphify/verify.sh`, then build. On no: proceed without it.

**After any task that adds, edits, or deletes code or files**, before the final report: refresh the **local** graph with `graphify update .` (re-extracts only changed files; tree-sitter, no API, seconds) so later queries stay accurate. The committed `graphify-out/graph.json` is this theme's own single-repo artifact: regenerating and committing it (`graphify update .` + `graphify cluster-only . --no-label --no-viz`) is a deliberate step when the structure changes meaningfully, not churn on every edit. The optional cross-repo `merge-graphs` (with `wp-framework`/`wp-tooling`) is a maintainer task - do not run it as part of a routine change. If graphify is not installed or no graph exists, say so in one line; never block the task on it.

## Guardrails (all AI tools) - BASE, non-negotiable

- **Never run history- or remote-affecting `git`/`gh`.** No `commit`, `push`, `branch -D`, `reset --hard`, `rebase`, `tag`, `git add` for a commit, PR create/merge, issue/PR comment, `gh secret set`, or any write to a remote or to git history. Surface every one of those as a developer action: print the exact command for the developer to run. Read/setup git is allowed: `git clone`, `git checkout`, `git status`, `git diff` (e.g. the pilot bootstrap's sibling clone) may run with consent.
- **Never do a destructive operation outside this theme directory.** Do not delete or overwrite existing files in sibling repos (`../wp-tooling`, `../wp-framework`, ...) or anywhere else on disk. Cloning a NEW sibling that does not already exist is additive and allowed; modifying or removing existing out-of-repo content is not.
- Never run a package manager (`npm install`, `composer require/update`) or `npm run build` without explicit consent; print the command instead. The one consented exception is `npm run init` (the theme's own setup script, on a clean tree). In-repo install steps and the pilot bootstrap (sibling clone + `file:`/`path` ref edits + installs) may run with consent.
- Never read, log, or transmit secret values.
- Never apply cross-file wiring without showing the diff and getting consent.
