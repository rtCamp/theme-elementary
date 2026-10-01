---
name: init
description: Set up this cloned theme-elementary into a named theme, or manage its identity and capabilities later. Installs the declared dependencies, accounting for the Composer npm hook. Drives `npm run init`. Always confirms before destructive or install steps; expects a clean working tree.
---

# init

Two jobs: **bootstrap + setup** a fresh clone into a real theme, or **manage** an already-set-up project. Always interactive: gather inputs first, confirm the resolved plan, then act.

## Use for
- First run: install declared dependencies, rename starter tokens, pick which example sets to keep.
- First run WITH a feature brief: after rename + capabilities, implement the described features by chaining to the **scaffold** skill (TDD), replacing the matching `Example*`/demo placeholders (step 8).
- Later: rename / re-prefix, toggle features (Tailwind, HMR, Dev Tools).

## Do not use for
- Adding a feature class → the **scaffold** skill (`npx wp-tooling add`).
- Bug fixes, refactors, hand-written features.
- Re-running init to change capabilities after first setup. Removal is one-shot (markers are consumed); a second run leaves dangling `Main::CLASSES` refs. Set the set ONCE; change it later via scaffold.

## Be interactive (required)
Ask the developer for every input the chosen path needs, in ONE batched message, before acting. Never assume a theme name. Show derived values back. Wait for explicit consent before any install, file rewrite, or destructive step. If an answer is missing, ask; do not guess.

## Plan and announce (required) - developer experience first
Keep the developer oriented at every moment; great DX is the goal even through an AI skill.

1. **State the plan up front** in one short message: the mode (setup / manage) and the ordered steps you will run for THIS request. For a setup-with-brief, that is: detect mode -> install deps -> gather + confirm identity and capabilities -> run init (which also removes the example sets the brief does not need) -> hand each feature to the scaffold skill.
2. **Maintain a live TODO list** on the host's task surface (`TodoWrite` in Claude Code): one entry per planned step, exactly one `in_progress` at a time, marked `completed` the moment it is done. Add entries as the work reveals them (a missing dep, each feature to scaffold).
3. **Announce each step with a short title** as you start it (e.g. "Running init", "Removing the shortcode example", "Handing the Testimonial block to scaffold") and report its outcome in one line. Never go silent during a long step.
4. **Confirm before anything destructive or installing** (the init rewrite, dependency installs), showing the exact resolved values.

## Steps

### 1. Detect mode
```bash
test -f .wp-scaffold.json && echo manage || echo setup
```
No `.wp-scaffold.json` → **setup**. Present → **manage** (read it for current identity; `npm run init -- --list` shows feature status).

### 2. Install dependencies
Skip installation when both dependency trees are already current. Otherwise, with installation consent, run from the theme directory:

```bash
nvm use
composer install
```

This theme's Composer `post-install-cmd` runs `npm i`; do not immediately repeat `npm install`. If Composer dependencies are already current but npm dependencies are missing or stale, run `npm install` with consent. If Composer was installed with `--no-scripts`, npm installation is still required. Check both dependencies before continuing. If consent is declined, print the missing installation command and stop.

### 3. Preconditions (verify; do not silently fix)
- `node_modules/@rtcamp/wp-tooling` exists (the engine needs it). If missing, surface the required installation command and stop.
- Clean working tree (`git status`). Init rewrites files irreversibly; a clean tree is the only undo. If dirty, ask to commit/stash.
- Setup only: confirm this is a clone meant to become a new theme, not the maintained starter theme. Manage mode operates on an already-personalized project.

### 4. Gather inputs
**Setup:** theme name (required, e.g. `Acme Blog` → namespace `rtCamp\Theme\Acme_Blog`, package `rtcamp/acme-blog`, text domain, constant/function/CSS prefixes, the `style.css` + `functions.php` headers; show these back); version (default `1.0.0`); which example sets to remove and which features to enable (defaults: keep all sets, hmr on, tailwind and dev-tools off). The engine derives the tokens itself; do not read the engine source to work them out - the mapping above is the contract, and the graph answers any deeper question (see the graphify policy in `AGENTS.md`).
**Manage:** which of identity / features to change.

### 5. Confirm
Show the exact resolved values and the exact command. Get explicit consent; init is destructive.

### 6. Run init (with consent)
Successful init changes also run `npm run sync-ai`; `--list`, help, failed runs, and interrupted runs do not.
```bash
# Setup:
npm run init -- --name="Acme Blog" --version=1.0.0 --yes \
  --remove-examples=shortcode,patterns --features=hmr,tailwind
# Manage:
npm run init -- --list
npm run init -- --enable=tailwind --yes
npm run init -- --enable=dev-tools --yes
npm run init -- --features=hmr --yes        # exact enabled set (empty = none)
```
- `--keep-examples` keeps all; `--remove-examples` (no value) removes all; `--remove-examples=a,b` removes listed keys.
- `--features=a,b` sets the exact enabled set; `--enable`/`--disable` are deltas. Do not combine an exact set with delta flags.
- `--yes` requires `--name` in setup mode; feature toggles in manage mode do not require a name. For the guided wizard, run bare `npm run init` (space toggles, enter confirms).

**Setup WITH a feature brief - remove the example sets in THIS step (do not make it a separate round-trip):** remove every example set (`--remove-examples` with all keys, or the no-value form). Unlike a plugin's per-kind `AbstractModule` with a `get_classes()` array to keep-and-empty, this theme lists every class directly in `Main::CLASSES`, so there is nothing to keep-and-empty: the scaffold skill builds each briefed feature from scratch and wires its new `::class` line into `Main::CLASSES` itself (step 8). Example: brief = a custom Testimonial block + a footer credits shortcode -> `--remove-examples=block-extension,settings,shortcode,components,patterns`, then let scaffold build the two real features.

### 7. After init
- Tailwind enabled → it added `src/css/frontend/tailwind.css` + `postcss.config.js` and pinned `@rtcamp/tailwind-config`; developer runs `npm install` (the feature changes declarations; it does not install packages).
- Dev Tools enabled → developer runs `composer update rtcamp/wp-dev-tools -W`, starts/restarts wp-env, activates the theme using the exact mounted directory name printed by the engine (`npm run wp-env run cli -- wp theme activate <directory-name>`), then runs `npm run dev:connect`. This optional package is private and requires repository access; WordPress 6.9+ and PHP 8.2+ are required. The feature writes a gitignored `.wp-env.override.json` with Query Monitor, MCP Adapter, local gates, and host/theme-container paths; it does not modify committed `.wp-env.json` or the tests environment.
- Dev Tools disable → disconnect first (`npm run dev:disconnect`), then disable and follow the engine’s Composer update instruction.
- `composer dump-autoload` runs during setup when `composer.json` is present.
- Trust the engine's own output to verify (it reports the removed sets, drops their `Main::CLASSES` lines and tests, and regenerates the autoloader). To confirm a symbol or reference, run a single `graphify query`/`affected` against the graph - never grep `Main.php`/`inc/` to check removal.
- If you hand-edited PHP and are NOT handing off to scaffold (e.g. a manage-mode dangling `Main::CLASSES` cleanup), run `composer phpcs` on the change and fix it. When a brief follows (step 8), leave the phpcs/PHPStan/test gates to the scaffold skill - init does not run them.
- **Refresh the LOCAL graph** with `graphify update .` (theme slice, tree-sitter, no API, seconds). The committed `graphify-out/graph.json` is a maintained baseline; your local refresh keeps queries accurate after a rename. If graphify is not installed, say so in one line; do not block.
- Report: new identity, example sets removed/kept, features toggled, outstanding developer actions.

### 8. Implement the brief by handing off to scaffold (setup only, when features were described)
By this point step 6 has removed the example sets. init's remaining job is purely to **hand each described feature to the scaffold skill** - it does NOT build the class, write or run tests, set up the test env (`npm run wp-env start`), or run the phpcs/PHPStan gates. The **scaffold skill takes over** and owns all of that.

For each described feature, **invoke the scaffold skill**, passing the brief (e.g. a dynamic `testimonial` block; a `[footer_credits]` shortcode). The scaffold skill owns conventions, the `wp-tooling add` call, wiring the new `::class` into `Main::CLASSES`, the TDD loop, test execution, and the gates - report its result; do not duplicate that work here.

**Pass your findings to scaffold (speeds it up).** init has already resolved the theme identity while renaming, so hand those values to the scaffold skill in the same message instead of making it re-derive them. State explicitly: the resolved **PHP namespace root** (e.g. `rtCamp\Theme\Acme_Blog`), **base_path** for module scaffolds (`inc/Modules/<Kind>`), **tests_namespace** (e.g. `rtCamp\Theme\Acme_Blog\Tests`), **tests_path** (`tests/php`), **text_domain** and **slug** (e.g. `acme-blog`), the **theme display name**, and that **classes wire directly into `Main::CLASSES`** (no per-kind module file). With these provided, the scaffold skill skips its own `composer.json` / `style.css` / `Main.php` discovery reads. It still reads source files when it needs exact code (the graph and a handoff are for orientation, not a substitute for reading).

End state: init has renamed and trimmed the example sets (step 6); the scaffold skill has implemented the real features.

## Capability model
ONE "Select the example sets to include" prompt. Each is keep-or-remove; removing deletes it entirely (concrete classes, its `Main::CLASSES` line + `use` import, coupled regions, demo files). The sets and features match `bin/scaffold.config.js`:

| Category | Keys |
|---|---|
| Editor & Frontend | `block-extension`, `shortcode`, `components`, `patterns`, `tailwind` (feature) |
| Admin | `settings` |
| Dev | `hmr`, `dev-tools` (features) |

- `block-extension` - Media-text block render-filter extension.
- `shortcode` - Author-bio shortcode.
- `components` - Example components (button, card).
- `patterns` - Page-creation block pattern.
- `settings` - Theme options settings page.

Sets are kept by default; pass keys to `--remove-examples` to drop. Features: `hmr` on, `tailwind` and `dev-tools` off; toggle via `--features`/`--enable`/`--disable`.

### Changing capabilities after setup
Add later → scaffold skill / `npx wp-tooling add <category>/<slug>` (writes the class, wires it into `Main::CLASSES`). Remove later → delete its class file(s) under `inc/Modules/<Kind>/` plus its `Main::CLASSES` line and `use` import by hand. Do not re-run init to change the set.

## Hard rules
- **BASE (see AGENTS.md guardrails):** never run history/remote `git`/`gh` (commit, push, `branch -D`, `reset --hard`, PR, issue comment, `gh secret set`) - print them as developer actions. `git clone`/`checkout` for setup are fine. Never do a destructive operation outside this theme directory; cloning a NEW sibling is additive and OK, deleting/overwriting existing out-of-repo files is not.
- Package managers and init only with consent. Step 2 describes the normal dependency installation; feature dependency updates require consent too.
- Never run a destructive init without confirming resolved values, on a clean tree. Non-interactive setup with `--yes` skips optional Git initialization.
- Never commit, push, open PRs, or edit `.wp-scaffold.json` by hand.
- Never invent flags. Supported: `--name`, `--version`, `--yes`, `--keep-examples`, `--remove-examples[=...]`, `--features`, `--enable`, `--disable`, `--reinit`, `--list` (optionally `--json`), `--clean`, `--help`. Run `npm run init -- --help` if unsure.

## Reference
- Engine: `@rtcamp/wp-tooling/init` (via `bin/init.js`). Capability map: `bin/scaffold.config.js`.
- Conventions renamed into: `AGENTS.md`, `.github/instructions/structure.instructions.md`.
- Local setup / features: `README.md`, `DEVELOPMENT.md`, `docs/hmr.md`, `docs/tailwind.md`, `docs/asset-building-process.md`.
- Graphify policy: `AGENTS.md`.
