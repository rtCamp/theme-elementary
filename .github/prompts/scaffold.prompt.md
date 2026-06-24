---
mode: agent
description: Add one feature (dynamic block, shortcode, settings/admin page, service, CI workflow - plus CPT/taxonomy/REST/CLI/cron for a companion plugin) to this theme via @rtcamp/wp-tooling, TDD-first. Derives test cases first, scaffolds via the engine, writes tests, implements to green, wiring into Main::CLASSES.
---

# /scaffold

Copilot equivalent of the Claude `scaffold` skill. Keep it in step with [`.claude/skills/scaffold/SKILL.md`](../../.claude/skills/scaffold/SKILL.md) (the fuller reference). Read [`AGENTS.md`](../../AGENTS.md) and [`.github/instructions/`](../instructions/) first.

## Be interactive (required for Copilot)

1. Confirm the scaffold target and the test-case checklist with the developer BEFORE scaffolding. Ask in one message; **stop and wait** for the reply.
2. Show every cross-file wiring edit (file + line + snippet) and get an explicit "yes" before applying it.
3. If the request is ambiguous or project patterns conflict, ask once with 1-3 concrete options. Never guess.

## Use for
Dynamic block, shortcode, settings/admin page, plain `Registrable` service, framework module, CI/CD workflow. The engine also covers CPT, taxonomy, REST controller, cron, CLI, user role - in a theme those belong in a **companion plugin** (flag the placement before scaffolding).

## Do not use for
Refactors, bug fixes, hand-written features, anything no scaffold covers, or presentation that belongs in `theme.json` / a block pattern / template part / block style.

## Steps

### 1. Discover
`npx wp-tooling list --json` → pick one `category/slug`. If ambiguous, ask with candidates. `origin: "remote"` entries fetch their manifest on first `add` (network I/O; can fail `EFETCHFAIL`).

### 2. Introspect (once per session)
Read: `composer.json` `autoload.psr-4` (root ns `rtCamp\Theme\Elementary\` → `inc/`) and `autoload-dev.psr-4` (tests ns `rtCamp\Theme\Elementary\Tests\` → `tests/php/`); `package.json` scripts (block build `--output-path`); theme entry `functions.php` (bootstrap class `Main` + `Main::CLASSES`); 2-3 existing implementations of the same/similar kind (e.g. `AuthorBio`, `ThemeOptions`). Confirm findings in one short message.

### 3. Canonical layout
Group files by **kind**, never by feature. `<Root>` = `rtCamp\Theme\Elementary`. Source `inc/Modules/<Kind>/`, ns `<Root>\Modules\<Kind>`, tests `tests/php/` (ns `<Root>\Tests`). **Wire the new `::class` into `inc/Main.php` `Main::CLASSES`** (plus its `use` import) - this theme lists classes directly there, with no per-kind `AbstractModule`/`get_classes()`. A multi-kind feature spans the per-kind dirs; each artifact wires its own `::class` in. A per-feature module folder (`Modules/<Feature>/...`) is an anti-pattern; flag it, do not add to it.

### 4. Derive test cases (BEFORE any scaffold call)
Checklist: happy path, edge cases (empty/missing/boundary), error paths (invalid input, missing auth/capability), kind-specific integration (e.g. `wp/block-dynamic`: block name, `register_hooks` action, `render()` markup, empty state; `wp/shortcode`: registered, output, escaping; `wp/settings-page`: registered under the menu, capability gate, `get_fields()`). Show the checklist; resolve add/remove with the developer before scaffolding.

### 5. Invoke the engine (never hand-write what it covers)
```bash
npx wp-tooling add wp/<kind> --non-interactive --json \
    --namespace='rtCamp\Theme\Elementary\Modules\<Kind>' --base_path=inc/Modules/<Kind> \
    --tests_namespace='rtCamp\Theme\Elementary\Tests' --tests_path=tests/php --text_domain=elementary-theme \
    --<input>=<value> ...
```
Always pass this theme's conventions (the engine defaults target a different layout). Apply project-sampled details (class suffix, sub-namespace, vendor prefix, build dir). `--dry-run` to preview. There is no `wp/module` step (no per-kind module file); each artifact wires into `Main::CLASSES`. Multi-kind: run in dependency order; re-read `ai.wiring` after each call. Result: `{ scaffold, engine, developer, ai, warnings }`.

### 6. Process the result
- `engine.wrote/skipped` → report. `developer.install.*` → print as copy-paste; **never run** `composer require`/`npm install` (the framework already ships - ignore a `composer require rtcamp/wp-framework` suggestion). `developer.secrets` → print as `gh secret set` checklist; **never read/write values**.
- `ai.wiring`: the engine miscomputes `targetFile` as a module file; the real target is `inc/Main.php`. Translate the snippet to a `<Class>::class,` line in `Main::CLASSES` plus the matching `use` import, mirroring the sampled entries. Show file + line + rendered snippet, get consent, insert idempotently.

### 7. TDD loop (mandatory)
Tests before implementation, no exceptions. A. Expand the engine stub into the full suite from §4; strip every `markTestIncomplete`. B. Run `npm run test:php` (PHP, under wp-env) / `npm run test:js` (JS); expect red. C. Implement just enough to flip ONE test green. D. Re-run. E. Loop B-D one test at a time. F. Refactor, re-run. G. Final gates: full PHPUnit, full Jest if JS touched, PHP compliance (§7a), `npm run lint:js`. For blocks, surface `npm run build` as a developer action before testing.

### 7a. PHP compliance (required, on changed PHP)
Write compliant PHP up front (`declare( strict_types = 1 );`, short arrays, full types, prefixed globals, `@package`/`@since`, `static::`). Then: `composer phpcs:fix` (phpcbf) → `composer phpcs` (the theme's `phpcs.xml.dist`) → `composer phpstan`. **Run the PHP linters inside wp-env** (the host PHP may be newer than the pinned WPCS and abort the sniffs). Resolve every finding in the generated code. Never silence a real issue with a blanket `phpcs:ignore`/`@phpstan-ignore`; if a fix is unclear or changes behaviour/API, STOP and ask.

### 8. Escalate when stuck (do not guess)
Stop and report (what you tried, observed, what's blocking, 1-3 options) after: 3 stuck C-D iterations on one test; a result contradicting your model (re-read the file); conflicting project patterns; ambiguity after one clarification.

### 9a. Refresh the knowledge graph
After all files are green, before the report: `graphify update .` to re-extract changed files. <=30 words to the developer. If not installed/built, say so in one line; do not block. See `AGENTS.md` (graphify).

### 9b. Final report
Files written (by directory), wiring applied (`inc/Main.php` line, `Main::CLASSES` entry + `use` import), tests authored + pass counts, lint result, graph refreshed (or skipped + reason), outstanding developer actions (installs, `npm run build`, secrets, CI branch protection).

## Hard rules
- Never write production code before its test exists on disk.
- Never hand-write an artifact the engine can scaffold.
- Never group multiple kinds under a per-feature folder.
- Never declare done without its test passing, or PHP done without §7a clean.
- Never silence a real phpcs/phpstan finding, weaken assertions, or leave `markTestIncomplete`/`@todo`.
- Never run `composer require`, `npm install`, `npm run build`, or `gh secret set` without consent.
- Never read/write/log/transmit secret values; never edit branch protection or repo settings.
- Never commit, push, open PRs, or apply wiring without showing the diff and getting consent.
- Never modify `composer.json`/`package.json`/lockfiles beyond what the engine wrote.

## Reference
- Engine contract / examples: `node_modules/@rtcamp/wp-tooling/docs/`.
- Fuller version of this workflow: `.claude/skills/scaffold/SKILL.md`.
