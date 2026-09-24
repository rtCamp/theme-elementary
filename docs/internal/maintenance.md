# Maintain the starter theme

This is the maintainer checklist for releasing a change to the starter itself: release validation, checks, local dependency development, documentation publishing, and the knowledge graph. Use a disposable project for initialization tests, kept outside the maintained checkout so its tests, package manifests, and generated files cannot be picked up by the main project's tooling. Never personalize the maintained starter theme.

## Release validation

Record `git rev-parse HEAD`, Node/PHP versions, the framework revision in `composer.lock`, and the tooling revision in `package-lock.json`.

1. Follow [Getting Started](../getting-started.md) for both the standalone and existing-WordPress routes, with a fresh dependency install from declared sources. Do not substitute a development engine for this check.
2. Run interactive init and a separate non-interactive setup. Check identity, retained examples, generated state, and optional Git decisions.
3. Exercise each example-removal group in a fresh fixture. Check both deleted files and the remaining registration array, then run `composer dump-autoload` and `php -l` for a fast syntax/autoload check before loading WordPress.
4. Enable/disable optional features. Install the changed declarations before checking Tailwind output or Dev Tools runtime integration. For Dev Tools, verify the development override does not change test configuration.
5. Start WordPress, activate the mounted theme directory, build, and check a source edit in the frontend and editor.
6. Follow the [CLI/AI scaffold example](../scaffolding.md) and the [manual integration examples](../../DEVELOPMENT.md). Verify paths, registration, behavior, and focused tests.
7. Build the documentation and check links before and after init cleanup.

If an installation route or feature cannot be exercised, report the missing prerequisite and affected acceptance criterion. Do not describe an untested path as verified. A local dependency override is a separate integration test, not a substitute for the declared-dependency route. The init engine's own output reports what it renamed, removed, and toggled; treat that as the primary verification signal rather than diffing `inc/` by hand.

### Cleanup checks

Check that the Copilot-specific `.github` files (`copilot-instructions.md`, `prompts/`, `instructions/`) and `languages` are removed, while workflows, issue templates, and the PR template stay untouched. Also confirm the init wrapper script is not removed by cleanup, and that `sync-ai` (see [initialization.md](../initialization.md#what-changes)) restores only generated framework instructions. Claude skills and documentation remain; Copilot prompts and theme-specific GitHub rules do not. Run POT generation separately to verify the language output can be recreated.

Check references in retained documentation and AI instructions. Use upstream source links for optional examples or starter-only files that cleanup may remove. Do not silently change the cleanup contract as part of a documentation correction.

## Checks

Use the explicit commands in [Local development](../local-development.md#check-a-change). The current `npm test` wildcard includes `test:js:watch`; `lint:all` matches fix commands and its own aggregate. Do not use those aggregates as a terminating, read-only validation gate. Correcting them belongs in a separate script change.

Record pre-existing failures separately from regressions. Keep temporary smoke tests and logs out of the starter theme's committed test suite unless they cover a maintained behavior that belongs there.

### Temporary validation procedures

Status recorded on **2026-09-15** for theme baseline `27860f1`, framework `v1.0.1` (`87774ee`), and tooling `18d003c`. Recheck these conditions against the revisions being validated.

| Active procedure | Why it is needed | Retire when |
| --- | --- | --- |
| Run the explicit commands under [Checks](#checks). | The aggregate scripts include watch, fix, or recursive commands. | Both aggregates terminate and run only their intended checks without modifying files. |
| Apply the [test-only `WP_DEBUG` override](../local-development.md#check-a-change). | Logger tests require debug mode, which wp-env disables in its test environment. | The maintained test setup passes the logger tests without a per-project override. |
| Log diagnostics instead of displaying them when checking login. | Early feature-description translation notices can interrupt login headers. | Translation timing is fixed and login succeeds with diagnostics displayed. |

### Record validation results

Put journey and link-check results in the validating PR description or release handoff, with links to logs and build artifacts. Include the date, theme commit (and any uncommitted changes), installed dependency revisions, Node/PHP versions, environment, and any local overrides.

Record one row per installation route, init mode, removal/feature selection, scaffold/manual example, build, and check: command or action, expected result, actual result, and **pass / fail / blocked** with evidence or a blocker. For documentation, record the builder revision, site navigation checks, and separate local-file/anchor and external-link results before and after cleanup.

Report implementation defects and new CI automation separately, with reproduction steps and affected revisions. Link follow-up issues here when they exist.

## Local dependency development

When deliberately testing a shared-package change, use a separate disposable theme and a separately managed dependency checkout. Install the theme normally first and record the dependency entries you will override.

Clone the dependency you need anywhere outside the theme, or reuse an existing checkout. Replace the absolute paths below with your chosen locations:

```bash
git clone https://github.com/rtCamp/wp-tooling.git '/absolute/path/to/wp-tooling'
git clone https://github.com/rtCamp/wp-framework.git '/absolute/path/to/wp-framework'
```

In the dependency checkout, select and record the revision under test. The tooling override below needs the source monorepo's `node-packages/wp-tooling/` directory; the `npm/wp-tooling` distribution branch has a different layout. Follow the [tooling](https://github.com/rtCamp/wp-tooling/blob/main/CONTRIBUTING.md#development-setup) or [framework](https://github.com/rtCamp/wp-framework/blob/main/CONTRIBUTING.md#development-setup) maintainer guide for that package's setup and checks.

From the disposable theme directory, point only the tooling declaration at your checkout:

```bash
npm pkg set 'devDependencies.@rtcamp/wp-tooling=file:/absolute/path/to/wp-tooling/node-packages/wp-tooling'
npm install --install-links
```

For framework work, add a local Composer `path` repository pointing to `/absolute/path/to/wp-framework` and run a targeted `composer update rtcamp/wp-framework -W`. Ensure the local package version satisfies this theme's `^1.0` constraint.

When done, restore only the dependency source/constraint and local repository entry, then regenerate the affected lockfile. Preserve identity changes made by init. Do not blanket-checkout `package.json` or `composer.json`, and do not commit absolute paths or local overrides. Recheck the declared-dependency install.

## Documentation publishing

Markdown under `docs/` is rendered by the [shared documentation workflow](https://github.com/rtCamp/action-docusaurus-build/blob/codex/documentation-workflow/README.md). The root Development and Contributing guides remain GitHub pages linked from the site. No Docusaurus dependencies or generated site files belong in this theme.

The caller supplies the main sidebar order; Markdown needs no sidebar metadata. Advanced guides remain linked from the relevant task pages. Internal pages are reached through Contributing and are omitted from the main sidebar.

The caller workflow builds documentation pull requests against any base. Pushes and manual runs on `theme-elementary-v2` can publish; other runs build only. Maintainers configure Settings → Pages → Source as **GitHub Actions** and allow `theme-elementary-v2` in the `github-pages` environment. No custom token is needed.

Keep the workflow ref pinned to a reviewed revision. When changing it, test the site with that revision, including its internally pinned builder. Update the publishing branch and trigger together when the release branch changes.

For local verification, install the shared builder in a separate checkout and use its CLI against this repository:

```bash
node /path/to/action-docusaurus-build/cli.mjs build \
  --source /path/to/theme-elementary \
  --repository rtCamp/theme-elementary \
  --out-dir /tmp/theme-elementary-docs
```

Check the homepage, navigation order, root-guide links, anchors, and mobile layout. The builder fails on broken internal links and missing repository-file links. Also check external links and root Markdown, which are not fully covered by the site build. Update incoming links whenever a guide is moved or removed, and [record the results](#record-validation-results).

## Knowledge graph

The graph is optional and may be stale. Verify exact behavior from current source when documenting it. For a deliberate artifact refresh, run local `graphify update .` and `graphify cluster-only . --no-label --no-viz`, then review the graph/report diff separately. Do not overwrite the theme's committed graph with a merged cross-repository graph during routine documentation work.

## Known gaps

Keep implementation follow-ups separate from documentation changes:

- Aggregate test/lint commands need the correction described under [Checks](#checks).
- Logger test setup and early feature-description translation need the corrections described under [Temporary validation procedures](#temporary-validation-procedures).
- Enabling Tailwind exposes existing check assumptions: one PHP test expects it disabled, CSS lint rules reject Tailwind directives, and manifest lint requires dependency keys sorted after a feature adds them. Verify the default state and report optional-feature check failures separately; do not hide them by changing the application feature selection for a normal developer test run.
- Cleanup removes AI files still named in retained starter instructions; the intended downstream Copilot/AI retention contract needs a separate change.

Record newly verified defects with their reproduction and affected revision in the review handoff. Missing upstream documentation belongs to that package; do not grow a duplicate manual here.
