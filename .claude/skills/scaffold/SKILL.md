---
name: scaffold
description: Add a scaffold (PHP class, dynamic block, shortcode, settings page, CI workflow) to this theme using @rtcamp/wp-tooling. TDD-first - derive test cases from the developer brief, scaffold via the engine, write tests, then implement to green under a red-green-refactor loop. Never runs package-manager commands or writes secret values; surfaces them as developer actions.
---

# scaffold

Drive `@rtcamp/wp-tooling`. Map the developer's request to a scaffold, derive test cases first, invoke the engine, expand tests, implement to green, report.

This skill is the canonical wp-tooling scaffold skill, tailored to this theme's structure (`inc/Modules/<Kind>`, namespace `<Root>\Modules\<Kind>`, tests in `tests/php/`) and the private-package pilot. **Wiring difference from a plugin:** this theme lists every class directly in `inc/Main.php` `Main::CLASSES` (no per-kind `AbstractModule` with a `get_classes()` array), so a new artifact's `::class` line is inserted into `Main::CLASSES` (and a `use` import added), not into a module file.

## Use for

Dynamic block, shortcode, settings/admin page, plain Registrable service, framework module, CI/CD workflow. The engine also covers CPT, taxonomy, REST controller, cron, CLI, and user role - in a theme those belong in a **companion plugin** (see §3), so use them only for a deliberate exception and flag the placement.

## Do not use for

Refactors, bug fixes, hand-written features, anything no scaffold covers. Theme presentation that belongs in `theme.json`, a block pattern, a template part, or a block style (those are not class scaffolds).

## Workflow

### 0. Plan and announce - before any other work

Before discovery, scaffolding, or coding, write a TODO list covering every step you intend to run for this task and show it to the developer. Use the host's task-tracking surface (`TodoWrite` in Claude Code; the host equivalent elsewhere).

Minimum entries:

1. Introspect project (§2).
2. Derive test-case checklist (§4) and confirm with developer.
3. Scaffold call(s) - one entry per kind for multi-kind features.
4. Apply wiring (§6a) - one entry per `ai.wiring` snippet, with consent.
5. Expand tests, run red (§7 A-B).
6. Implement to green (§7 C-E), one test at a time.
7. Refactor + final gates (§7 F-G).
8. Report (§9).

Update the list in real time. Mark each entry `in_progress` before starting it and `completed` the moment it is done. Exactly one entry `in_progress` at any time. Add new entries when the work reveals them (e.g. a stuck-loop escalation).

This makes progress legible to the developer and gives them a stable surface to interrupt or re-prioritise.

### 1. Discover

```bash
npx wp-tooling list --json
```

Result: `{ scaffolds: [{ id, slug, category, kind, origin, counts }, ...] }`. Pick one `category/slug`. If ambiguous, ask the developer with candidates. Never guess. Entries with `origin: "remote"` live in another repo: their manifest is fetched on the first `add`, so `counts` is `null` here and the kind is always `template`. That first `add` does network I/O and can fail with `EFETCHFAIL` (see Engine errors) - treat a remote scaffold exactly like a local one once it resolves; the only difference is the fetch.

### 2. Introspect once per session (cache result)

**Reuse `/init`'s findings if it handed off to you in this session.** When `/init` chained here, you already established the resolved identity and conventions while running init - root namespace, base path (`inc/`), tests namespace + path (`<Root>\Tests` -> `tests/php/`), text domain, and constant prefix. They are facts you set, not guesses: reuse them and SKIP the `composer.json` / `style.css` / `Main.php` reads below that only recover them. You still read a sample implementation for the registration pattern (next paragraph); sample it from a remaining example under `inc/Modules/` or from the framework abstract for the kind (`vendor/rtcamp/wp-framework/inc/Contracts/Abstracts/Abstract<Kind>.php`); do not guess.

**The graph saves tokens for orientation only; read the file for any data you copy.** This repo commits a queryable graph (`graphify-out/graph.json`). Use it to LOCATE things cheaply so you open fewer files: which classes implement a kind, what references a symbol, the path between two classes - `graphify query "..."`, `graphify explain "<Class>"`, `graphify affected "<Class>"`, `graphify path "A" "B"`. The graph is structural (not the full source), so it is NOT a source of truth for the patterns you act on. For registration shape, exact namespace, how `Main::CLASSES` lists classes, and wiring location, **read the actual file** - 100% accuracy beats a few saved tokens. (If you or init changed code this session, refresh the local slice first with `graphify update .`, seconds - AGENTS.md graphify policy.)

Read, in order (use the graph to find them fast; read the files for their contents):

- `composer.json` -> `autoload.psr-4` (root namespace + base path; here `rtCamp\Theme\Elementary\` -> `inc/`). `autoload-dev.psr-4` for the tests namespace (`rtCamp\Theme\Elementary\Tests\` -> `tests/php/`).
- `package.json` -> scripts. For block scaffolds, parse `build:blocks` for `--output-path=<DIR>` and pass as `--build_dir`. Default `assets/build/blocks`.
- Theme entry (`functions.php`) -> bootstrap class (`Main`) + `Main::CLASSES` (the list of every loaded class - this is where new classes wire in).
- 2-3 existing implementations of the same or a similar kind under `inc/Modules/<Kind>/` (e.g. `AuthorBio`, `ThemeOptions`, `MediaTextInteractive`): registration pattern, class-name suffix, sub-namespace, and how each is listed in `Main::CLASSES`. **Read these files** - they are the source of truth for the patterns you copy; use `graphify` only to find them.
- Block scaffolds: sample one `src/blocks/*/block.json` for vendor prefix and source dir.
- CI scaffolds: sample one `.github/workflows/*.yml` for filename and trigger style.

Anchors (`// scaffold:<kind>:classes`) are hints, not ground truth. Sampled patterns win.

Confirm findings with the developer in one short message. Proceed on confirmation.

### 3. Canonical layout

Files group by **kind**, never by feature. `<Root>` = this theme's `composer.json` `autoload.psr-4` root (`rtCamp\Theme\Elementary` -> `inc/`); tests autoload via `<Root>\Tests` -> `tests/php/`. **The engine's own defaults target a different layout (`includes/...`, `Inc\...`, `tests/...`), so you MUST pass this theme's conventions on every `add` (§5).** Every kind wires into `inc/Main.php` `Main::CLASSES` (§6a) - there is no per-kind module file to edit.

**Theme-appropriate kinds (lead with these):**

| Scaffold | Source dir | Source ns | Test dir / ns | Wire into |
|---|---|---|---|---|
| `wp/block-dynamic` | `inc/Modules/Blocks/` + `src/blocks/<slug>/` + `assets/build/blocks/<slug>/` | `<Root>\Modules\Blocks` | `tests/php/` / `<Root>\Tests` | `inc/Main.php` (`Main::CLASSES`) |
| `wp/shortcode` | `inc/Modules/Shortcodes/` | `<Root>\Modules\Shortcodes` | `tests/php/` / `<Root>\Tests` | `inc/Main.php` (`Main::CLASSES`) |
| `wp/settings-page` | `inc/Modules/Settings/` | `<Root>\Modules\Settings` | `tests/php/` / `<Root>\Tests` | `inc/Main.php` (`Main::CLASSES`) |
| `wp/admin-page` | `inc/Modules/Admin/` | `<Root>\Modules\Admin` | `tests/php/` / `<Root>\Tests` | `inc/Main.php` (`Main::CLASSES`) |
| `wp/registrable` | `inc/Modules/<Module>/` | `<Root>\Modules\<Module>` | `tests/php/` / `<Root>\Tests` | `inc/Main.php` (`Main::CLASSES`) |

**Belongs in a companion plugin, not a theme** (`wp/cpt`, `wp/taxonomy`, `wp/rest`, `wp/cli`, `wp/cron`, `wp/user-role`): these register content types, endpoints, commands, or roles that WordPress guidelines place in a plugin - a theme switch must not drop a site's content or REST API. The engine still scaffolds them with the same conventions (`inc/Modules/<Kind>/`, ns `<Root>\Modules\<Kind>`, tests `tests/php/`, wire into `Main::CLASSES`), so use them only for a companion plugin or a deliberate, stated exception - and flag the placement to the developer before scaffolding.

Match the existing directory case exactly (`REST`, `CLI`, not `Rest`/`Cli`).

**Modules host one kind each. No `Modules/<Feature>/...`.** A multi-kind feature spans the per-kind directories; each artifact wires its own `::class` into `Main::CLASSES`.

If the project already has a per-feature module folder, flag as anti-pattern. Offer migration before adding new artifacts. Do not scaffold into it.

### 4. Derive test cases from the developer brief - BEFORE any scaffold call

Write a test-case checklist covering:

- **Happy path** - central behaviour the developer stated.
- **Edge cases** - empty/missing/boundary inputs, large input.
- **Error paths** - invalid input, missing auth, wrong capability.
- **Integration** - kind-specific:
  - `wp/block-dynamic`: block name, `register_hooks` action, `render()` markup with a `WP_Query` fixture, empty state, count cap, attribute filters.
  - `wp/shortcode`: shortcode registered, output for valid attrs, escaping, default attrs, override by child theme (if it renders via `Util::get_template()`).
  - `wp/settings-page` / `wp/admin-page`: page registered under the right menu, capability gate, `get_fields()` (settings) as the single source of truth, REST exposure.
  - `wp/registrable`: hooks registered via `register_hooks()`, shared retrieval if `Shareable`.
  - (companion-plugin kinds) `wp/cpt`: `post_type_exists()`, supports, REST exposure, attached taxonomies. `wp/rest`: route in `rest_get_server()->get_routes()`, permission check, schema. `wp/cron`: `wp_next_scheduled()`, callback fires, unschedule. `wp/cli`: `WP_CLI::add_command` registered, `__invoke`, dry-run.

Show the checklist to the developer. Ask: confirm, add, remove? Resolve before scaffolding. This is the cheapest place to catch a misread requirement.

### 5. Apply conventions, invoke the engine

Always invoke the engine for any kind it covers. Hand-writing is not a substitute.

**Always pass this theme's conventions (§3)** - the engine defaults target a different layout. With `<Root>` = `rtCamp\Theme\Elementary` (or the renamed root) and `<Kind>` = the module dir:

```bash
npx wp-tooling add wp/<kind> --non-interactive --json \
    --namespace='rtCamp\Theme\Elementary\Modules\<Kind>' --base_path=inc/Modules/<Kind> \
    --tests_namespace='rtCamp\Theme\Elementary\Tests' --tests_path=tests/php --text_domain=elementary-theme \
    --slug=<slug> --class=<Class> --<other-inputs>=<value> ...
```

Per-scaffold inputs are not listed by `--help`; run once with `--dry-run` to discover required inputs (a block needs `slug`/`title`; a shortcode needs `tag`/`class`; a settings page needs `slug`/`title`). Dry-run preview: append `--dry-run`. Never use the interactive wizard.

**Multi-kind / dependency order:**

1. Artifacts in dependency order (e.g. a block or shortcode that queries a CPT comes after the CPT).
2. Re-read `ai.wiring` after each call.

(There is no `wp/module` step: this theme has no per-kind `AbstractModule`. Each artifact wires into `Main::CLASSES` directly.)

Result shape: `{ scaffold, engine, developer, ai, warnings }`.

### 6. Process the result

| Block | Action |
|---|---|
| `engine.wrote` / `engine.skipped` | Already on disk. Report. |
| `developer.install.composer` / `developer.install.npm` | Print as copy-paste command. **Never run `composer require` / `npm install`.** Pilot notes: npm installs need `npm install --install-links`; the framework (`rtcamp/wp-framework`) already ships, so ignore a `composer require rtcamp/wp-framework` suggestion. |
| `developer.secrets` | Print as `gh secret set` checklist. **Never read/write/log/transmit values.** |
| `ai.wiring` | Adaptive wiring with consent (see 6a). |
| `ai.tests` | Mandatory expansion under TDD loop (see 7). |
| `warnings` | Print to developer. |

#### 6a. Adaptive wiring

For each `{ targetFile, anchor, snippet, description }`:

0. **Target file** - the engine computes `targetFile` as a per-kind module file (e.g. `inc/Modules/<Kind>.php`), which this theme does NOT use. The real wiring target is **`inc/Main.php`** - add the new class to `Main::CLASSES` and add its `use` import.
1. **Snippet** - the canonical snippet adds the class to a module's `get_classes()`; translate it to this theme: a new `<Class>::class,` line in the `Main::CLASSES` array plus the matching `use rtCamp\Theme\Elementary\Modules\<Kind>\<Class>;` import, mirroring the sampled entries (e.g. `AuthorBio`, `ThemeOptions`). Show both. If patterns conflict, ask.
2. **Location** - `Main::CLASSES` carries no `// scaffold:<kind>:classes` anchor, so insert the `::class` after the last existing entry in the array (and add the `use` import in the import group, alphabetical with the others). Anchor present -> after it; else skip and print as a manual instruction.
3. **Consent** - show targetFile (`inc/Main.php`) + line range + description + rendered snippet (both the `Main::CLASSES` line and the `use` import). Ask `[apply / different location / edit snippet / skip]`. Never apply without consent.
4. **Idempotent** - search first, do not re-insert.

### 7. TDD loop (mandatory for every scaffolded artifact)

Tests come before implementation. No exceptions. If the developer says "skip tests", explain the policy and decline.

For block scaffolds, surface a developer action before testing: "run `npm run build` so the editor can read the compiled block." Do not run it yourself.

| Step | Action |
|---|---|
| A | Expand the engine's stub into the full suite from §4's checklist. Strip every `markTestIncomplete`. |
| B | Run: `npm run test:php` (PHP, under wp-env) / `npm run test:js` (JS). Expect red. PHP tests need the WP test env: if the runner cannot connect, surface `npm run wp-env start` as a developer action and retry. |
| C | Implement just enough production code to flip **one** failing test green. |
| D | Re-run. Confirm that one test passes. |
| E | Loop B-D one test at a time. |
| F | Once green, refactor; re-run. |
| G | Final gates - all must pass: full PHPUnit suite (`npm run test:php`), full Jest suite if JS touched (`npm run test:js`), `composer phpcs:fix` (phpcbf), `composer phpcs` (phpcs), `composer phpstan` (PHPStan), `npm run lint:js`. **Run the PHP linters inside wp-env** - the host PHP may be newer than the pinned WPCS supports and will abort the sniffs (see Pilot environment). Never silence a real phpcs/phpstan finding with a blanket ignore; fix it, or escalate (§8). |

Frameworks per kind:

| Kind | Framework |
|---|---|
| `wp/shortcode`, `wp/settings-page`, `wp/admin-page`, `wp/registrable` (and companion-plugin `wp/cpt`/`wp/taxonomy`/`wp/cron`/`wp/cli`/`wp/rest`) | PHPUnit |
| `wp/block-dynamic` | Jest (edit.js) + PHPUnit (render method) |
| `block/interactive` | Jest + Playwright |
| `ci/*` | actionlint + yaml-parse |

### 8. Escalate when stuck - do not guess

Stop and report findings to the developer when any of the following holds. Wait for response before continuing.

- 3 consecutive iterations of step C-D on the same test without progress.
- A test result contradicts your model of the code (likely hallucination - re-read the file on disk before guessing again).
- Sampled project patterns conflict and you cannot resolve which to follow.
- A `ai.wiring` snippet's anchor and project pattern both differ from canonical.
- Developer requirements remain ambiguous after one round of clarification.

Escalation report format: **what you tried, what you observed, what's blocking, 1-3 specific resolution options.** Do not keep iterating in silence.

### 9. Refresh the graph, then report

Once green, refresh the LOCAL graph so it reflects the new artifacts (and any later query stays accurate): `graphify update .` (tree-sitter, seconds; AGENTS.md graphify policy).

Then report:

- Files written, grouped by top-level directory.
- Wiring applied: `inc/Main.php` line, the `Main::CLASSES` entry + `use` import added.
- Tests authored and pass count per file.
- Lint + PHPStan result.
- Outstanding developer actions: composer / npm installs (pilot: `--install-links`), `npm run build` (blocks), secrets to set, branch-protection note (CI).

## Pilot environment + engine quirks (this theme)

**Test env + linters (`wp-env`):**
- Always use `npx wp-env` (or `node_modules/.bin/wp-env`), never bare `wp-env`.
- **Run PHP linters inside wp-env (PHP 8.2).** The host PHP may be newer than the pinned `wp-coding-standards/wpcs` supports, which makes the sniffs throw deprecation errors and abort. Run e.g. `npx wp-env run cli --env-cwd=/var/www/html/wp-content/themes/$(basename "$PWD") -- vendor/bin/phpcs <files>` (PHPStan tolerates newer PHP, so `composer phpstan` is fine on the host).
- If `wp-env start` reports a port already allocated, start on free alternates: `WP_ENV_PORT=8890 WP_ENV_TESTS_PORT=8891 npm run wp-env start` (find a free pair with `lsof -nP -iTCP:<port> -sTCP:LISTEN`). The theme's default ports are 5890 (dev) / 5891 (tests).
- `wp-env start` can flake on a transient image pull (TLS timeout); one retry is allowed, and exit 0 does not mean "up" - confirm the start output reports success.
- `pretest:php` runs `composer install` in the `cli` container; if `npm run test:php` fails on it, run PHPUnit directly: `npx wp-env run cli --env-cwd=/var/www/html/wp-content/themes/$(basename "$PWD") -- vendor/bin/phpunit -c phpunit.xml.dist`.

**Generated-code quirks (write the code right up front; these survive `composer phpcs:fix`):**
- **Fully-qualify WP global classes** (`\WP_Error`, `\WP_REST_Request`, `\WP_REST_Response`) everywhere they appear - in code AND docblocks - with NO `use` statement for them. Reason: `composer phpcs:fix` force-qualifies `WP_Error` (Slevomat `FullyQualifiedExceptions` treats `*Error` as an exception) and then strips the now-unused imports, including docblock-only ones; PHPStan (scanning `inc/`) then reports `class.notFound`. Writing them fully-qualified avoids the fix -> phpstan round-trip.
- **(companion-plugin) `wp/rest` overridden controller methods must match WP core's untyped signatures.** Adding a parameter type to a `WP_REST_Controller` override is a fatal LSP/contravariance violation that crashes `wp-env start`. Rewrite every overridden method with untyped params/return and express the types in PHPDoc only.
- **(companion-plugin) `wp/rest` route is double-versioned.** The generated `$namespace` already includes the version yet `register_routes()` appends `/v{version}` again. Pick one source and fix the generated test's asserted route too.
- The engine emits compact `declare(strict_types=1);` and test files with no `@package` tag or per-method doc comments. Run `composer phpcs:fix` (phpcbf) for the spacing, then hand-add the `@package` file tag + a one-line doc comment on each `test_*` method (PHPCS requires them; phpcbf does not add them).
- **File-header order:** the file docblock must immediately follow `<?php`, *before* `declare(strict_types=1);`. If the engine emits `declare` first, move the docblock above it. phpcbf does not reorder this for you.
- **Narrow always-non-null `?string` overrides.** Several framework abstracts type a method `?string` with a `null` default (e.g. `AbstractAdminPage::get_parent_slug()`, `AbstractBlock::get_block_dir()`). When your override always returns a concrete value, declare the override `: string` (covariant narrowing) and give it a `@return string` tag.

**Judge your work scoped, not repo-wide.** Run the gates against the files you authored (`vendor/bin/phpcs <files>`, `vendor/bin/phpstan analyse <files>`) and scope `composer phpcs:fix` to them; a repo-wide non-zero exit is not necessarily your code. The theme ships gate-clean (in wp-env), so any finding outside your files is a regression to report, not ambient noise.

## Hard rules - never violate

- **BASE (see AGENTS.md guardrails):** never run history/remote `git`/`gh` (commit, push, `branch -D`, `reset --hard`, PR, issue comment, `gh secret set`, `gh repo edit`); print them as developer actions. `git clone`/`checkout` are fine. Never do a destructive operation outside this theme directory.
- Never write production code before its test exists on disk.
- Never hand-write an artifact the engine can scaffold.
- Never group multiple kinds under a per-feature folder (`Modules/<Feature>/...`).
- Never declare an artifact done without its test file passing.
- Never declare PHP done without the §7 G gates (`composer phpcs:fix` -> `composer phpcs` -> `composer phpstan`) clean on the generated code.
- Never lower assertion strength to make a test pass (`assertTrue(true)`, widened types).
- Never delete or skip a test the developer would expect to pass.
- Never leave `markTestIncomplete`, `markTestSkipped`, or `@todo` in committed state.
- Never run `composer require`, `npm install`, `npm run build`, or any write-side CLI without explicit consent.
- Never read, write, log, or transmit secret values.
- Never edit branch protection, repo settings, webhooks, or any GitHub admin surface.
- Never apply wiring without showing the diff and getting consent.
- Never invent a third registration pattern when canonical and sampled disagree - ask.
- Never restore scaffold anchor comments without explicit consent.
- Never modify `composer.json`, `package.json`, or any lockfile beyond what the engine wrote.

**Detect-and-correct:** if you notice an earlier artifact in the wrong directory (e.g. `includes/...` from unpassed conventions) or missing its test file, stop new work, migrate to the canonical layout (§3), add the missing tests, then resume.

## Engine errors

| Code | Response |
|---|---|
| `ENOSCAFFOLD` | Surface `available` list, suggest closest, ask. |
| `EMISSINGINPUT` | Read `missingDetails`, run §2 discovery, retry with resolved values. |
| `EBADSCAFFOLD` | Invalid manifest. Surface verbatim, do not retry. For an `origin: "remote"` scaffold this means the fetched manifest at its pinned ref is broken - surface it; do not try to repair another repo's scaffold. |
| `EWRITEFAIL` | Surface path + errno. Do not retry. |
| `ERENDERFAIL` | Scaffold author bug. Surface. |
| `EFETCHFAIL` | Network/HTTP failure fetching an `origin: "remote"` scaffold's manifest or a template. Surface `url` + `statusCode`. If the payload sets `rateLimited`, tell the developer to set `WP_TOOLING_GITHUB_TOKEN` and stop. A timeout is transient - one retry is reasonable; a 404 means the source pin is wrong - surface, do not retry. Never hand-write the artifact to work around a failed fetch (the engine owns it). |
| Unknown | Surface, exit non-zero, do not crash. |

## CI/CD variant

- `ai.wiring` usually empty.
- `developer.secrets` usually populated. For multi-workflow setups, emit one consolidated `gh secret set` checklist at the end (dedupe).
- `ai.tests` framework is `actionlint` or `yaml-parse`. Validate; do not fill the YAML.

## Reference

- Engine contract: `node_modules/@rtcamp/wp-tooling/docs/ai-orchestration.md`
- Examples: `node_modules/@rtcamp/wp-tooling/docs/examples.md`
- Engine source: `node_modules/@rtcamp/wp-tooling/src/scaffolds/`
- Test templates: `node_modules/@rtcamp/wp-tooling/scaffolds/wp/<kind>/templates/test.php.mustache`
