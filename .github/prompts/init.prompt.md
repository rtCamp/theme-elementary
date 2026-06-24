---
mode: agent
description: Set up this cloned theme-elementary into a named theme, or manage its identity and capabilities later. During the pilot it can also run the local bootstrap (sibling clone of the tooling engine, local package ref, composer + npm install). Drives `npm run init`.
---

# /init

Copilot equivalent of the Claude `init` skill. Keep it in step with [`.claude/skills/init/SKILL.md`](../../.claude/skills/init/SKILL.md). Read [`AGENTS.md`](../../AGENTS.md) first for conventions and guardrails.

Two jobs: **bootstrap + setup** a fresh clone into a real theme, or **manage** an already-set-up project.

## Be interactive (required for Copilot)

Copilot must gather inputs BEFORE acting. Do not assume any value.

1. Ask the developer every question the chosen path needs, in ONE message.
2. **Stop and wait** for the answers. Do not run commands, edit files, or proceed until they reply.
3. Echo the resolved/derived values back and get an explicit "yes" before any install, file rewrite, or destructive step.
4. If an answer is missing or ambiguous, ask again. Never guess.

## Use for
- First run: optional pilot bootstrap (deps), rename starter tokens, pick which example sets to keep.
- Later: rename / re-prefix, toggle features (Tailwind, HMR).

## Do not use for
- Adding a feature class → the `/scaffold` prompt.
- Bug fixes, refactors, hand-written features.
- Re-running init to change capabilities after first setup (removal is one-shot; a second run leaves dangling `Main::CLASSES` refs). Change the set later via `/scaffold` or by hand.

## Steps

### 1. Detect mode
Run: `test -f .wp-scaffold.json && echo manage || echo setup`. No file → **setup**. Present → **manage** (`npm run init -- --list` shows feature status).

### 2. Setup: offer the pilot bootstrap
The rtCamp tooling packages are private/unpublished during the pilot. If `npm install` resolves `@rtcamp/wp-tooling` and `composer install` resolves `rtcamp/wp-framework` from its VCS repo, skip this. Otherwise ask: "Bootstrap local dependencies now? (clones the tooling engine, points npm at it, installs). y/n". On **yes**, with consent, run in order (explain each step in <=30 words):

```bash
nvm use
git clone git@github.com:rtCamp/wp-tooling.git ../wp-tooling
( cd ../wp-tooling && git checkout release/v1.0.0 )
npm pkg set "devDependencies.@rtcamp/wp-tooling=file:../wp-tooling/node-packages/wp-tooling"
# wp-framework resolves from the VCS "repositories" entry already in composer.json.
# For local framework dev, optionally swap it for a path repo:
#   { "type": "path", "url": "../wp-framework", "options": { "symlink": false } }
composer update rtcamp/wp-framework
npm install --install-links                                     # fallback: add --legacy-peer-deps
```
Add the `@rtcamp/tailwind-config` `file:` ref too only if Tailwind will be enabled. The `file:`/`path` edits are local-only: tell the developer to revert ONLY those dependency-source lines before committing (keep the identity changes init wrote). On **no**, skip to step 3 and surface installs as developer actions.

### 3. Preconditions (verify; do not silently fix)
- `node_modules/@rtcamp/wp-tooling` exists. If missing and bootstrap declined, surface `npm install` as a developer action.
- Clean working tree (`git status`). Init rewrites files irreversibly; a clean tree is the only undo. If dirty, ask to commit/stash.
- Confirm this is a fresh clone meant to become a new theme, not the skeleton repo itself.

### 4. Gather inputs
**Setup:** theme name (required, e.g. `Acme Blog` → namespace `rtCamp\Theme\Acme_Blog`, package `rtcamp/acme-blog`, text domain, prefixes, the `style.css` + `functions.php` headers; show these back); version (default `1.0.0`); which example sets to remove and which features to enable (defaults: keep all sets, hmr on, tailwind off).
**Manage:** which of identity / features to change.

### 5. Confirm
Show the exact resolved values and the exact command. Get explicit consent; init is destructive.

### 6. Run init (with consent)
`npm run init` also runs `npm run sync-ai`; expected.
```bash
npm run init -- --name="Acme Blog" --version=1.0.0 --yes --remove-examples=shortcode,patterns --features=hmr,tailwind
# Manage:
npm run init -- --list
npm run init -- --enable=tailwind --yes
npm run init -- --features=hmr --yes        # exact enabled set (empty = none)
```
- `--keep-examples` keeps all; `--remove-examples` (no value) removes all; `--remove-examples=a,b` removes listed keys.
- `--features=a,b` sets the exact enabled set; `--enable`/`--disable` are deltas. `--yes` requires `--name`.

### 7. After init
- Tailwind enabled → it added `src/css/frontend/tailwind.css` + `postcss.config.js` and pinned `@rtcamp/tailwind-config`; developer runs `npm install` (re-apply the `file:` ref first during the pilot).
- Suggest `composer dump-autoload`.
- If you hand-edited PHP under `inc/`, run `composer phpcs:fix` → `composer phpcs` → `composer phpstan` and resolve every finding (run the PHP linters inside wp-env if the host PHP is newer than the pinned WPCS). Never silence a real issue; if unclear, STOP and ask.
- Refresh the local knowledge graph: `graphify update .`. If not installed, say so in one line; do not block. See `AGENTS.md` (graphify).
- Report: new identity, example sets removed/kept, features toggled, outstanding developer actions.

## Capability model
ONE keep-or-remove prompt; removing deletes the set entirely (classes, its `Main::CLASSES` line + `use` import, coupled regions, demo files). Matches `bin/scaffold.config.js`.

| Category | Keys |
|---|---|
| Editor & Frontend | `block-extension`, `shortcode`, `components`, `patterns`, `tailwind` (feature) |
| Admin | `settings` |
| Dev | `hmr` (feature) |

Sets kept by default; pass keys to `--remove-examples` to drop. Features: `hmr` on, `tailwind` off.

## Hard rules
- Package managers only with consent: `npm run init`, and the pilot bootstrap of step 2. Otherwise surface install commands as developer actions.
- Never run a destructive init without confirming resolved values, on a clean tree.
- Never commit, push, open PRs, or edit `.wp-scaffold.json` by hand.
- Tell the developer to revert local-only `file:`/`path` edits before commit.
- Never invent flags. Supported: `--name`, `--version`, `--yes`, `--keep-examples`, `--remove-examples[=...]`, `--features`, `--enable`, `--disable`, `--reinit`, `--list`, `--clean`, `--help`. Run `npm run init -- --help` if unsure.
