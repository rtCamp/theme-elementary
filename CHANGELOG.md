# Changelog

All notable changes to this project are documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!-- In the starter, release-please writes each release below from the Conventional Commit pull request titles merged to main, so don't add entries under `## Unreleased` here. Projects made from the starter keep `## Unreleased` for `npm run release:changelog`. -->

## Unreleased

## [2.0.1](https://github.com/rtCamp/theme-elementary/compare/v2.0.0...v2.0.1) (2026-11-11)


### Fixed

* **ci:** separate file and project lint commands ([#834](https://github.com/rtCamp/theme-elementary/issues/834)) ([0bb2118](https://github.com/rtCamp/theme-elementary/commit/0bb211824cb7cad5ba802cb4662fa86534f0fcf6))
* **deps:** pin webpack-dev-server to 6.0.0 to stop the block editor reload loop ([#846](https://github.com/rtCamp/theme-elementary/issues/846)) ([c908371](https://github.com/rtCamp/theme-elementary/commit/c908371b699b280fcee6fd3603b0ba3b25e4288a))
* **deps:** require patched Node for CVE-2026-111497 ([#820](https://github.com/rtCamp/theme-elementary/issues/820)) ([58aaf12](https://github.com/rtCamp/theme-elementary/commit/58aaf12c2795414c6d6e95fe75ffdaece910cfce))
* **deps:** update @wordpress/scripts to 37 and refresh stale npm overrides ([#842](https://github.com/rtCamp/theme-elementary/issues/842)) ([3aa32b9](https://github.com/rtCamp/theme-elementary/commit/3aa32b948da54756f704c565b3a18f186ac4ed1b))
* **deps:** update dev dependencies with security advisories ([#839](https://github.com/rtCamp/theme-elementary/issues/839)) ([d09415e](https://github.com/rtCamp/theme-elementary/commit/d09415e0151116c1480c6cf0c68a2f0a8777c8e8))


### Changed

* **deps-dev:** Bump browserslist from 4.29.3 to 4.29.5 ([#832](https://github.com/rtCamp/theme-elementary/issues/832)) ([84a51b1](https://github.com/rtCamp/theme-elementary/commit/84a51b104b8ef6218f5caa38f3fa59f33a393579))
* **deps-dev:** Bump dotenv from 18.0.4 to 18.0.9 ([#829](https://github.com/rtCamp/theme-elementary/issues/829)) ([6e0f0b5](https://github.com/rtCamp/theme-elementary/commit/6e0f0b57f6e1446f19a56c6a1742d02c113326a3))
* **deps-dev:** Bump eslint from 10.11.0 to 10.14.0 ([#837](https://github.com/rtCamp/theme-elementary/issues/837)) ([9629c1e](https://github.com/rtCamp/theme-elementary/commit/9629c1e10a99a1007e569a24acefa4a9351d67d8))
* **deps-dev:** Bump eslint-plugin-jest from 29.16.6 to 29.17.0 ([#831](https://github.com/rtCamp/theme-elementary/issues/831)) ([e3158bc](https://github.com/rtCamp/theme-elementary/commit/e3158bc05d9569d06b2b13aee158341c9502ce88))
* **deps-dev:** Bump globals from 17.6.0 to 17.14.0 ([#838](https://github.com/rtCamp/theme-elementary/issues/838)) ([4ad8bc6](https://github.com/rtCamp/theme-elementary/commit/4ad8bc6e20278e8481726d4158baa3bf4728f012))
* **deps-dev:** Bump phpstan/phpstan-phpunit from 2.0.21 to 2.1.1 ([#826](https://github.com/rtCamp/theme-elementary/issues/826)) ([c0ad46a](https://github.com/rtCamp/theme-elementary/commit/c0ad46a66c7754a2f0514e6749b6f32468baa8f9))
* **deps-dev:** Bump wp-phpunit/wp-phpunit from 7.1.1 to 7.1.3 ([#825](https://github.com/rtCamp/theme-elementary/issues/825)) ([07bdb0b](https://github.com/rtCamp/theme-elementary/commit/07bdb0b245773372fb0790bce722c6a434243bfb))

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
