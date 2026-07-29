---
name: setup
description: Bootstrap a WordPress plugin or theme from a natural-language description. Detects project type, applies tooling (EditorConfig, PSR-4, PHPCS, PHPStan, ESLint, Stylelint, PHPUnit, Jest, pa11y), and chains feature scaffolds (blocks, shortcodes, settings pages, etc.) in one session. Asks for clarification whenever intent is ambiguous - never assumes.
---

# setup

You are configuring a WordPress plugin or theme from a natural-language request. Your job is to understand exactly what the developer wants, turn that into a sequenced plan of scaffold invocations, confirm the plan, and execute it. You never assume anything you cannot verify by reading the project directory.

This skill is meant to be **copied verbatim** into the user's repo at `.claude/skills/setup/SKILL.md`. It assumes `@rtcamp/wp-tooling` is on the `PATH` (via `npx`) or installed as a project dev dependency.

## When to use this skill

Use when the developer asks to:

- "Set up my plugin/theme" or "scaffold this as a [description]."
- Bootstrap a new empty project directory.
- Add rtCamp standard tooling to an existing project.
- Create a feature scaffold (block, shortcode, settings page, etc.) as part of a setup session.

Do not use this skill for: isolated bug fixes, one-off file edits, or anything outside the `@rtcamp/wp-tooling` scaffold catalogue.

## Workflow

### 0. Parse the request

Read the developer's message carefully. Extract:

- **Project type**: plugin vs theme.
- **Standards**: VIP vs non-VIP vs WordPress.org vs plain WordPress.
- **Languages needed**: PHP, JS (blocks, scripts), CSS/SCSS.
- **Features wanted**: block, shortcode, settings page, integrations, etc.
- **Tests wanted**: PHPUnit (PHP unit/integration), Jest (JS), pa11y (a11y), or none.
- **Any specific names, namespaces, or requirements** mentioned by the developer.

If the request is vague or any of the above is unclear, **stop and ask before reading the project directory**. Do not try to infer project type from the directory if the developer's message already says it. Do not proceed with gaps.

Example clarifying questions (ask all at once, never drip):

```
Before I start, I need a few details:

1. Is this a WordPress plugin or a theme?
2. Will this be deployed on WordPress VIP? (Determines the PHPCS standard.)
3. What is the PHP root namespace you want to use? (e.g. rtCamp\\Theme\\AcmeBlog)
4. What directory holds the PHP source? (e.g. inc/ or src/)
5. Do you want tests set up? If so: PHPUnit for PHP, Jest for JS, pa11y for a11y - any or all?
6. You mentioned a block - what should it render? (I need a slug/title.)
```

### 1. Detect what already exists

After the request is clear, read the project directory to avoid duplicating work.

**Project type (verify against developer's description):**

```bash
grep -rl "Plugin Name:\|Theme Name:" . --include="*.php" --exclude-dir=vendor --exclude-dir=node_modules -l | head -1
```

**VIP indicators:**

```bash
grep -rl "WordPress-VIP-Minimum\|automattic/vip-coding-standards\|VIP_GO_ENV" . \
    --include="*.{php,xml,json,yml}" --exclude-dir=vendor --exclude-dir=node_modules | head -3
```

**Languages present:**

```bash
find . -name "*.php" -not -path "*/vendor/*" | head -1
find . \( -name "*.js" -o -name "*.jsx" \) -not -path "*/node_modules/*" -not -path "*/build/*" | head -1
find . \( -name "*.scss" -o -name "*.css" \) -not -path "*/node_modules/*" | head -1
```

**PSR-4 autoload:**

```bash
grep -A 8 '"autoload"' composer.json 2>/dev/null
```

**Existing tooling configs (skip if present):**

```bash
ls .editorconfig phpcs.xml.dist phpstan.neon.dist eslint.config.mjs .stylelintrc.json \
   phpunit.xml.dist jest.config.js .pa11yci.json 2>/dev/null
```

**Existing namespace (if PSR-4 missing):**

```bash
grep -r "^namespace " . --include="*.php" --exclude-dir=vendor | head -3
```

If detection contradicts what the developer said, flag it and ask. Never silently override the developer's stated intent with what you find on disk.

### 2. Build the scaffold plan

Construct the plan in two phases: **project setup** and **feature scaffolds**.

#### Phase A: Project setup

| Condition | Scaffold | Skip if |
|---|---|---|
| Always | `setup/editorconfig` | `.editorconfig` exists |
| PHP present, no PSR-4 in `composer.json` | `setup/psr4` | `autoload.psr-4` already set |
| VIP project | `lint/phpcs/vip` | `phpcs.xml.dist` exists |
| Non-VIP project | `lint/phpcs/full` | `phpcs.xml.dist` exists |
| Developer explicitly chose core-only PHPCS | `lint/phpcs/core` | `phpcs.xml.dist` exists |
| PHP present | `lint/phpstan` | `phpstan.neon.dist` exists |
| JS present | `lint/eslint` | `eslint.config.mjs` exists |
| CSS or SCSS present | `lint/stylelint` | `.stylelintrc.json` exists |
| Developer wants PHP tests | `setup/phpunit` | `phpunit.xml.dist` exists |
| Developer wants JS tests | `setup/jest` | `jest.config.js` exists |
| Developer wants a11y tests | `setup/pa11y` | `.pa11yci.json` exists |

#### Phase B: Feature scaffolds

Map each feature the developer mentioned to one or more scaffold IDs from the catalogue (theme-appropriate first):

| Developer said | Scaffold ID |
|---|---|
| Gutenberg block | `wp/block-dynamic` |
| Shortcode | `wp/shortcode` |
| Settings / options page | `wp/settings-page` |
| Admin page | `wp/admin-page` |
| Plain service | `wp/registrable` |
| CI pipeline | `ci/cd-wporg` (or other CI scaffold) |
| (companion plugin) CPT / taxonomy / REST / cron / CLI | `wp/cpt` / `wp/taxonomy` / `wp/rest` / `wp/cron` / `wp/cli` |

Run `npx wp-tooling list --json` to see exactly what is available. If a feature the developer wants has no matching scaffold, note it explicitly as a manual task in the final report. For a theme, flag CPT/taxonomy/REST/cron/CLI requests as companion-plugin work (WordPress guidelines keep content and endpoints out of themes) before scaffolding them.

For each feature scaffold, you need the same project-convention information as the `scaffold` skill requires (namespace, base path, class suffix, registration pattern). Collect this once from the project and cache it.

### 3. Confirm the full plan before doing anything

Show the complete two-phase plan. Be specific: include the scaffold ID, what file(s) it writes, and any inputs it will use.

```
Here is what I will do. Please confirm or adjust before I start.

Phase A - Project setup:
  1. setup/editorconfig    → .editorconfig
  2. setup/psr4            → wiring in composer.json  (namespace: rtCamp\Theme\AcmeBlog, path: inc/)
  3. lint/phpcs/full       → phpcs.xml.dist
  4. lint/phpstan          → phpstan.neon.dist
  5. lint/eslint           → eslint.config.mjs
  6. setup/phpunit         → phpunit.xml.dist, tests/bootstrap.php

Phase B - Feature scaffolds:
  7. wp/shortcode          → inc/Modules/Shortcodes/FooterCredits.php
                             (namespace: rtCamp\Theme\AcmeBlog\Modules\Shortcodes,
                              wires its ::class into Main::CLASSES)

Skipped (already present): none.

Not in catalogue (manual tasks): none.

Confirm? Or adjust (e.g. "remove phpunit", "use vip phpcs", "add jest")?
```

Do not start running scaffolds until the developer confirms. If they adjust, update the plan and confirm once more before starting.

### 4. Execute Phase A

Run each setup scaffold in order. Use `--non-interactive --json --cwd .`.

For `setup/editorconfig`:

```bash
npx wp-tooling add setup/editorconfig --non-interactive --json --cwd .
```

For `setup/psr4` (use detected or developer-supplied namespace and base path):

```bash
npx wp-tooling add setup/psr4 \
    --non-interactive --json --cwd . \
    --namespace='rtCamp\Theme\AcmeBlog' --base-path='inc'
```

For lint and test setup scaffolds:

```bash
npx wp-tooling add lint/phpcs/full --non-interactive --json --cwd .
npx wp-tooling add lint/phpstan     --non-interactive --json --cwd .
npx wp-tooling add lint/eslint      --non-interactive --json --cwd .
npx wp-tooling add setup/phpunit    --non-interactive --json --cwd . --source-dir=inc
npx wp-tooling add setup/pa11y      --non-interactive --json --cwd . --base-url=http://localhost:8888
```

Process each result before running the next:

- Report files written and skipped.
- Apply `setup/psr4` wiring to `composer.json` with explicit consent (see §Wiring below).
- Accumulate `developer.install.*` and `developer.scripts.*` across all scaffolds.

### 5. Execute Phase B

For each feature scaffold, follow the full workflow from `scaffold/SKILL.md` - introspect conventions, apply naming, invoke the engine, adaptive wiring, **expand test stubs into a real suite, then drive implementation from those tests** (red → green → refactor).

Do not batch feature scaffolds. Run one at a time, apply its wiring (into `Main::CLASSES`), complete the TDD loop (see `scaffold/SKILL.md` §7), then move to the next.

For `wp/shortcode`, pass the same project conventions detected in Stage 1 (namespace, base path) - the engine defaults assume a different layout and will be wrong for any other project:

```bash
npx wp-tooling add wp/shortcode \
    --non-interactive --json --cwd . \
    --namespace='rtCamp\Theme\AcmeBlog\Modules\Shortcodes' --base_path='inc/Modules/Shortcodes' \
    --tests_namespace='rtCamp\Theme\AcmeBlog\Tests' --tests_path=tests/php \
    --tag=footer_credits --class=FooterCredits
```

The engine emits `ai.wiring` (where to register the class) and a thin test stub. Show the wiring snippet, get consent, apply it into `Main::CLASSES` (plus the `use` import). Then turn the stub into a real test suite (happy path, attrs, escaping, edge cases) and implement test-by-test until the suite is green. Never leave `markTestIncomplete` in the final state.

**Code quality (required).** For any PHP a feature scaffold writes or changes under `inc/` or `tests/`, run the compliance pipeline once the implementation is green, exactly as in the `scaffold` skill (`scaffold/SKILL.md` §7 G): `composer phpcs:fix` (phpcbf) -> `composer phpcs` (the project's `phpcs.xml.dist` ruleset) -> `composer phpstan` (the project's `phpstan.neon.dist`), resolving every finding in the generated code. **Run the PHP linters inside wp-env** when the host PHP is newer than the pinned WPCS. Write it compliant in the first place (`declare( strict_types = 1 );`, short arrays, typed signatures, prefixed globals, documented). If a fix is unclear or would change behaviour, public API, or intent, STOP and ask - never silence a real issue with a blanket `phpcs:ignore` / `@phpstan-ignore`.

**Phase B feature scaffolds require the matching test framework from Phase A.** If Phase A skipped `setup/phpunit` because the developer did not ask for tests, surface this before running Phase B feature scaffolds:

```
You asked for a shortcode, but Phase A skipped setup/phpunit.
The wp/shortcode scaffold ships a PHPUnit stub I cannot run without it.

Options:
  1. Add setup/phpunit now (recommended - I drive feature development from tests).
  2. Proceed without tests (stub will be written but not executed; I will note this as a manual follow-up).

Which?
```

Default to (1). Only proceed without tests if the developer explicitly chooses (2).

### Wiring: composer.json PSR-4

When `setup/psr4` wiring is received:

1. Show the current `"autoload"` block in `composer.json` (or note it is absent).
2. Show the intended entry: namespace `rtCamp\Theme\AcmeBlog` maps to `inc/`.
3. Ask: `Apply PSR-4 autoload to composer.json? [apply / skip]`
4. If apply: paste the engine's `ai.wiring[0].snippet` verbatim. The engine already emits the PSR-4 key JSON-encoded with its trailing backslash (e.g. `"rtCamp\\Theme\\AcmeBlog\\"`) - **do not escape it again**, or you will double the backslashes and break autoload.
5. Remind: run `composer dump-autoload --optimize` after applying.

If `composer.json` does not exist, offer to create a minimal one:

```
composer.json not found. I can create a minimal one:

  {
    "name": "rtcamp/acme-blog",
    "type": "wordpress-theme",
    "require": { "php": ">=8.2" },
    "autoload": {
      "psr-4": { "rtCamp\\Theme\\AcmeBlog\\": "inc/" }
    }
  }

Create it? [yes / skip PSR-4 / give me the values to use]
```

### Wiring: feature scaffolds

Same as `scaffold/SKILL.md` §6a - `ai.wiring`. The wiring target is `inc/Main.php` `Main::CLASSES` (this theme lists classes directly there, not in a per-kind module). Show diff, get consent, apply.

### 6. Consolidated final report

```
Setup complete.

Files written (Phase A):
  .editorconfig
  phpcs.xml.dist
  phpstan.neon.dist
  eslint.config.mjs
  phpunit.xml.dist
  tests/bootstrap.php
  composer.json        (PSR-4 autoload added, rtCamp\Theme\AcmeBlog → inc/)

Files written (Phase B):
  inc/Modules/Shortcodes/FooterCredits.php
  tests/php/inc/Modules/Shortcodes/FooterCreditsTest.php

Wiring applied:
  inc/Main.php - added FooterCredits::class to Main::CLASSES (+ use import).

Tests:
  tests/php/inc/Modules/Shortcodes/FooterCreditsTest.php - passing.

Developer actions (run these yourself):

  composer dump-autoload --optimize

  (any composer require / npm install lines the scaffolds reported)

Scripts to add (shown, not applied): the project's lint/test scripts.

Skipped: none.
Outstanding manual tasks: none.
```

Deduplicate packages. Sort alphabetically within each block. Pinned packages use exact versions; everything else uses range specifiers.

## PHPCS standard reference

| Scaffold ID | When to use | Ruleset |
|---|---|---|
| `lint/phpcs/full` | Most rtCamp projects (recommended default) | `vendor/rtcamp/wp-framework/phpcs.xml.dist`, WordPress-Core + Extra + Docs + VIP-Go |
| `lint/phpcs/vip` | WordPress VIP platform projects | `WordPress-VIP-Minimum` + `WordPress-Docs` |
| `lint/phpcs/core` | Projects explicitly opting out of VIP-Go rules | `WordPress` - Core + Extra + Docs only |

Developers can add `<rule>` entries to `phpcs.xml.dist` to override or extend the selected standard.

## Test scaffolds reference

| Scaffold ID | What it installs | When to apply |
|---|---|---|
| `setup/phpunit` | `phpunit.xml.dist`, `tests/bootstrap.php`, PHPUnit + polyfills | PHP plugin or theme with tests |
| `setup/jest` | `jest.config.js`, `@wordpress/jest-preset-default` | JS blocks or scripts with unit tests |
| `setup/pa11y` | `.pa11yci.json`, `pa11y-ci` | Any project needing WCAG2AA accessibility coverage |

## Rules: never assume, always ask

Before running any scaffold, every required input must be confirmed by the developer or verified from the project files. The following facts require explicit confirmation or verification - never infer them silently:

- Plugin vs theme.
- VIP vs non-VIP.
- PHP root namespace and base directory.
- Block slug and vendor prefix.
- Shortcode tag and class name.
- Settings/admin page slug and title.
- pa11y base URL.

If the developer's request is clear enough that a fact can be read unambiguously from the project (e.g. namespace from `composer.json` autoload, VIP from existing `phpcs.xml.dist`), no need to ask - cite the source in the confirmation plan instead.

## Hard prohibitions

You **must never** (BASE, see AGENTS.md guardrails):

- Run history/remote `git`/`gh` at all (commit, push, `branch -D`, `reset --hard`, PR, issue comment, `gh secret set`); print them as developer actions. `git clone`/`checkout` for setup are fine.
- Do a destructive operation outside the project directory (cloning a NEW sibling is additive and OK; deleting/overwriting existing out-of-repo files is not).
- Commit or push `graphify-out/graph.json`; local graph refreshes (`graphify update .`) stay local.
- Run `composer require`, `npm install`, `composer dump-autoload`, or any package manager command without explicit user approval.
- Edit `composer.json` scripts or `package.json` scripts - show them, let the developer apply.
- Apply wiring to any file without showing the diff and receiving consent.
- Apply more scaffolds than the confirmed plan.
- Silently skip a scaffold; always report skips with a reason.
- Set up CI/CD - that requires a separate `scaffold` skill invocation targeting `ci/` scaffolds.
- Declare generated PHP done without running the §5 code-quality pipeline (`composer phpcs:fix` -> `composer phpcs` -> `composer phpstan`) clean, or silence a real finding with a blanket `phpcs:ignore` / `@phpstan-ignore` to pass it.
- Commit, push, or open PRs without explicit approval.

## Error handling

For `ENOSCAFFOLD`: The requested scaffold id does not exist. Surface the `available` list, show the closest match, and ask the developer what to do.

For `EMISSINGINPUT`: Read `missingDetails`, collect the values from the project or ask the developer, and retry with the resolved values.

For `EWRITEFAIL`: Surface the path and OS error. Ask whether to retry (e.g. after the developer fixes permissions) or skip the file.

For `EBADSCAFFOLD`: Scaffold author bug. Surface verbatim. Do not retry.

## Reference

- Worked conversation examples: `node_modules/@rtcamp/wp-tooling/docs/examples.md`.
- One-off scaffold additions are handled by the companion `scaffold` skill (`scaffold/SKILL.md`).
