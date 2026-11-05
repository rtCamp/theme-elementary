# Changelog

All notable changes to this project are documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!-- In the starter, release-please writes each release below from the Conventional Commit pull request titles merged to main, so don't add entries under `## Unreleased` here. Projects made from the starter keep `## Unreleased` for `npm run release:changelog`. -->

## Unreleased

## [2.0.1](https://github.com/rtCamp/theme-elementary/compare/v2.0.0...v2.0.1) (2026-11-05)


### Fixed

* **ci:** separate file and project lint commands ([#834](https://github.com/rtCamp/theme-elementary/issues/834)) ([0bb2118](https://github.com/rtCamp/theme-elementary/commit/0bb211824cb7cad5ba802cb4662fa86534f0fcf6))
* **deps:** require patched Node for CVE-2026-111497 ([#820](https://github.com/rtCamp/theme-elementary/issues/820)) ([58aaf12](https://github.com/rtCamp/theme-elementary/commit/58aaf12c2795414c6d6e95fe75ffdaece910cfce))

## 2.0.0 - 2026-10-01

v2 rebuilds the starter on rtCamp's shared WordPress stack: runtime code from [`rtcamp/wp-primitives`](https://github.com/rtCamp/wp-primitives), and setup, scaffolding, releases and lint configs from [`@rtcamp/wp-tooling`](https://github.com/rtCamp/wp-tooling) and the `@rtcamp` npm packages. It is a new starter, not an in-place upgrade: themes built on 1.x keep working as they are.

### Added

- `npm run init` turns the starter into your theme: name, namespace, text domain and prefixes, which example sets to keep, and which optional features to enable. Run it again later to manage features.
- Feature scaffolding with `npx wp-tooling add` (custom post types, blocks, REST controllers, WP-CLI commands, settings pages and more), with tests for what it generates.
- Optional development features: HMR (BrowserSync live reload for the frontend and React Fast Refresh for blocks) and Tailwind CSS v4 with theme tokens generated from `theme.json`.
- Theme services on `rtcamp/wp-primitives` ^2.1: asset loading, components, template parts, runtime feature flags under Settings → Features, logging and encryption.
- Example sets that init can keep or remove: button and card components, an interactive media-text block extension, a theme options settings page, an author bio shortcode and a page-creation pattern.
- PHPUnit and Jest suites, PHPCS with `rtcamp/wp-phpcs`, PHPStan with `rtcamp/wp-phpstan`, and ESLint and Stylelint with `@rtcamp/eslint-config` and `@rtcamp/stylelint-config`.
- CI through [rtCamp/wp-shared-workflows](https://github.com/rtCamp/wp-shared-workflows) (`@v1`): lint, Jest, build, and PHPUnit across PHP 8.2 to 8.4 and WordPress 6.5 to 7.0.
- AI tooling: shared conventions in `AGENTS.md`, Claude Code skills (`/init`, `/scaffold`, `/setup`), GitHub Copilot prompts and instructions synced from wp-primitives, and a committed knowledge graph in `graphify-out/`.
- Documentation, published at [opensource.rtcamp.com/theme-elementary](https://opensource.rtcamp.com/theme-elementary/).
- This changelog, and `release:bump` / `release:changelog` scripts provided by `@rtcamp/wp-tooling`.

### Changed

- **Breaking:** PSR-4 PHP under `inc/` (`Core/`, `Modules/`, `Abstracts/`, `Helpers/`) in the `rtCamp\Theme\Elementary` namespace, with a `Main` bootstrap and an `Autoloader`. Replaces 1.x's `inc/classes`, `inc/helpers` and `inc/traits`.
- **Breaking:** sources live in `src/` and build to `assets/build/`, with blocks under `assets/build/blocks/`. Builds use `@wordpress/scripts` 36.
- **Breaking:** requires PHP 8.2+, WordPress 6.6+ and Node.js 22.22.2+.
- **Breaking:** the Composer package is now `rtcamp/theme-elementary`.
- PHP tests run in their own wp-env environment (`.wp-env.tests.json`), so they never touch the development site.

## 1.0.1 - 2022-08-29

### Added

- Script and style injection samples.
- Theme screenshot images.

## 1.0.0 - 2022-08-26

First release: block templates, template parts and patterns, asset bundling with the WordPress coding standards, PHPCS and PHPCBF scripts, PHPUnit and Jest setups, and theme initialization.
