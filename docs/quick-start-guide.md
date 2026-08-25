# Quick Start Guide

Turn this skeleton into a named theme and add features. Two tracks: **AI skills**
(guided, recommended) or the **raw CLI** (manual).

Worked example throughout: a **Northwind** theme with a `featured-posts` dynamic
block, a `[reading_time]` shortcode, and a theme options settings page.

## At a glance

| Task | AI skill (recommended) | Raw CLI |
|------|------------------------|---------|
| Set up the theme | `/init` + brief | `npm run init` |
| Add a feature | `/scaffold` + description | `npx wp-tooling add wp/<kind>` |

- **init:** names the theme and selects which example sets ship. Once per repo.
- **scaffold:** adds one feature (block, shortcode, settings/admin page, service, ...), wired into `Main::CLASSES`.

## Prerequisites

- Node (see [`.nvmrc`](../.nvmrc)), PHP 8.2+, and Docker (Docker powers the `wp-env` test site).
- For the AI track: [Claude Code](https://claude.com/claude-code) with the repo open.

> **Using a different AI assistant?** This guide's AI track shows Claude Code's
> `/init` and `/scaffold` skills, but the project ships shared instructions for
> GitHub Copilot and any other AI too: [AGENTS.md](../AGENTS.md) is the source of
> truth, with [`CLAUDE.md`](../CLAUDE.md) and
> [`.github/copilot-instructions.md`](../.github/copilot-instructions.md) as thin
> pointers to it. Copilot has matching prompt files in
> [`.github/prompts/`](../.github/prompts/) (`/init`, `/scaffold`); with another
> tool, describe the task and use the [raw CLI track](#track-b-raw-cli-no-ai).

## Local setup (pilot)

The one rtCamp npm package this theme needs, `@rtcamp/wp-tooling`, is **private**
during the pilot (served from GitHub Packages); `rtcamp/wp-framework` resolves
from the VCS repository already declared in [`composer.json`](../composer.json).

- **AI track:** do step 1 only. `/init` ([Track A](#track-a-ai-skills-recommended)) runs the rest for you.
- **Manual track:** do all the steps, then use [Track B](#track-b-raw-cli-no-ai).

```bash
# 1. Clone the skeleton's pilot branch, then enter it.
git clone -b feat/scaffold-engine git@github.com:rtCamp/theme-elementary.git northwind && cd northwind
nvm use                                         # Node from .nvmrc
```

> **On the AI track, stop here**, open the repo in Claude Code, and go to [Track A](#track-a-ai-skills-recommended).

```bash
# 2. Resolve @rtcamp/wp-tooling. Either (a) supply a GitHub token with read:packages...
export GITHUB_TOKEN=<your token>                # @rtcamp scope -> GitHub Packages (see .npmrc)
# ...or (b) sibling-clone wp-tooling and point npm at it (local-only):
git clone git@github.com:rtCamp/wp-tooling.git ../wp-tooling
( cd ../wp-tooling && git checkout release/v1.0.0 )
npm pkg set "devDependencies.@rtcamp/wp-tooling=file:../wp-tooling/node-packages/wp-tooling"

# 3. Install. wp-framework comes from its VCS repo; --install-links copies the
#    file: package (option b) so its peer deps resolve.
composer update rtcamp/wp-framework
npm install --install-links                     # add --legacy-peer-deps on ERESOLVE
```

> **Reverting before commit:** the option-(b) `file:` edit is local-only, but `init`
> also writes your theme's identity (name, namespace) into
> [`package.json`](../package.json) and [`composer.json`](../composer.json). Do not
> blanket-revert those files. Revert only the dependency-source line (the
> `@rtcamp/wp-tooling` `file:` ref) and [`composer.lock`](../composer.lock).

## Track A: AI skills (recommended)

### 1. Set up the theme

Open the repo in Claude Code. Run `/init` with the theme name and a one-line brief:

```
/init Set up a "Northwind" theme: a featured-posts block that lists recent
featured posts, a reading_time shortcode, and a theme options settings page.
```

The skill, confirming with you at each step:

1. Runs the local bootstrap (resolves `@rtcamp/wp-tooling`, installs).
2. Renames the skeleton to Northwind (namespace, text domain, `style.css` + `functions.php` headers).
3. Removes the example sets you don't need.
4. Hands each feature to scaffold, which writes the test first, generates the
   class, wires its `::class` into `Main::CLASSES`, and runs the gates.

Result from one sentence: a `featured-posts` block, a `[reading_time]` shortcode,
and a theme options page, each tested and passing `phpcs` / `phpstan` / `phpunit`.

### 2. Add a feature later

Run `/scaffold` with the feature in plain words:

```
/scaffold Add a "Newsletter signup" block with an email field and a privacy note.
```

The skill picks the right kind (a dynamic block), writes the test, scaffolds and
wires the class into `Main::CLASSES`, and runs the gates before reporting back.

> **Themes vs companion plugins:** a theme should not register content types,
> endpoints, or CLI commands (switching themes must not drop a site's content).
> If you ask `/scaffold` for a CPT, taxonomy, REST controller, cron, or CLI
> command, it flags the placement and suggests a companion plugin before
> proceeding.

## Track B: Raw CLI (no AI)

### 1. Initialize

```bash
npm run init                                    # guided wizard
# or non-interactive (the brief builds new features, so drop every example set):
npm run init -- --name="Northwind" --version=1.0.0 --yes --remove-examples
npm run init -- --list                          # example-set / feature status (manage mode)
```

The wizard renames the theme and trims the example sets; implementing the
features comes next, by hand or via scaffold.

### 2. Add the features

Append `--dry-run` to preview a command and to discover a kind's required inputs.
Each scaffold writes the class under `inc/Modules/<Kind>/`; wire its `::class`
into [`inc/Main.php`](../inc/Main.php) `Main::CLASSES` (the engine's `ai.wiring`
shows the exact line).

```bash
# Featured-posts dynamic block
npx wp-tooling add wp/block-dynamic --non-interactive --json \
  --namespace='rtCamp\Theme\Northwind\Modules\Blocks' --base_path=inc/Modules/Blocks \
  --tests_namespace='rtCamp\Theme\Northwind\Tests' --tests_path=tests/php \
  --text_domain=northwind --slug=featured-posts --title="Featured Posts" --class=FeaturedPosts

# Reading-time shortcode
npx wp-tooling add wp/shortcode --non-interactive --json \
  --namespace='rtCamp\Theme\Northwind\Modules\Shortcodes' --base_path=inc/Modules/Shortcodes \
  --tests_namespace='rtCamp\Theme\Northwind\Tests' --tests_path=tests/php \
  --text_domain=northwind --tag=reading_time --class=ReadingTime

# Theme options settings page
npx wp-tooling add wp/settings-page --non-interactive --json \
  --namespace='rtCamp\Theme\Northwind\Modules\Settings' --base_path=inc/Modules/Settings \
  --tests_namespace='rtCamp\Theme\Northwind\Tests' --tests_path=tests/php \
  --text_domain=northwind --slug=northwind-options --title="Theme Options" --class=ThemeOptions
```

The CLI generates each class and a test stub, and reports where to wire it. You
add the business logic and assertions, wire the `::class` into `Main::CLASSES`,
then run the gates when you are ready.

## AI skills vs raw CLI

The example above shows the difference: [Track A](#track-a-ai-skills-recommended)
built all three features from one sentence, while
[Track B](#track-b-raw-cli-no-ai) reached the same result through precise
commands and hands-on tests, wiring, and gates. Both are valid; they trade
convenience for control.

| Aspect | AI skills (`/init`, `/scaffold`) | Raw CLI (`npm run init`, `wp-tooling add`) |
|---|---|---|
| Local bootstrap | Handled for you | Run manually, with full control |
| Example-set selection (init) | Chosen from your brief | Chosen in the wizard or via flags |
| Conventions and inputs | Inferred (namespace, paths, text domain) | Specified explicitly |
| Wiring | Inserted into `Main::CLASSES` for you (with consent) | You add the `::class` line + `use` import |
| Code written | Implemented from your brief and codebase context | Scaffolded with a stub; logic is yours |
| Tests | Written first (TDD) and run for you | Provided as a stub to complete |
| Quality gates (`phpcs` / `phpstan`) | Run and fixed for you | Run at your discretion |
| Output | Generated from intent, worth a quick review | Deterministic, exactly as specified |

Rule of thumb: use the CLI for a single artifact you can fully specify; use the
skills for multi-feature setup, unfamiliar conventions, or when you want the
tests and gates handled for you.

## Code consistency and standards

Consistency is a primary requirement: code should read the same no matter who, or
what, writes it. The AI track is built around that.

- **The same standards, enforced every run.** Generated code is checked and fixed
  against the project's gates, the exact ones a developer runs locally:
  - PHP style: [`phpcs.xml.dist`](../phpcs.xml.dist) (the WordPress Theme Coding Standards).
  - PHP static analysis: [`phpstan.neon.dist`](../phpstan.neon.dist).
  - JS: [`eslint.config.mjs`](../eslint.config.mjs). CSS: [`.stylelintrc.json`](../.stylelintrc.json).
  - Run the PHP linters inside `wp-env` when the host PHP is newer than the pinned WPCS.
- **Verified, not just formatted.** The skill writes the test first and runs it,
  then `composer phpcs:fix` -> `composer phpcs` -> `composer phpstan`, fixing what
  it can. A feature ships passing the gates, not merely looking right.
- **No drift between developers.** Registration, wiring, and lifecycle come from
  the shared [`rtcamp/wp-framework`](https://github.com/rtCamp/wp-framework)
  abstracts, so the structure is identical whoever generates it.

The conventions these gates enforce are documented in [AGENTS.md](../AGENTS.md).

## Command reference

| Action | Command |
|--------|---------|
| Set up (guided) | `/init <name + brief>` |
| Set up (CLI) | `npm run init` |
| Example-set / feature status | `npm run init -- --list` |
| Add feature (guided) | `/scaffold <description>` |
| Add feature (CLI) | `npx wp-tooling add wp/<kind> ... --dry-run` |
| Build assets | `npm start` (watch) / `npm run build:prod` |
| Run PHP tests | `npm run test:php` |
| Lint + analyse | `composer phpcs && composer phpstan` |
