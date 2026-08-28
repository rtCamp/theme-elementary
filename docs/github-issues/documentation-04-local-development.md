# [Docs] Add a complete local-development workflow

Parent epic: `#TBD`

Suggested labels: `documentation`, `local-development`, `developer-experience`

## Problem

The skeleton ships `.wp-env.json`, asset builds, BrowserSync, block HMR, PHPUnit, Jest, PHPCS, PHPStan, ESLint, and Stylelint, but developers must piece the working sequence together from several files.

## Scope

Create `docs/local-development.md` as the canonical daily-development guide. Consolidate or link the existing asset and HMR reference material.

## Proposed structure

1. **Start and stop WordPress**
   - `npm run wp-env start`, stop, clean, and status commands.
   - Development and test ports from `.wp-env.json`.
2. **Open and verify the site**
   - Site/admin locations, theme activation behavior, and a CLI verification command.
3. **Configure local environment variables**
   - Copy `.env.local.example`, set `WP_HOST`, and explain optional values.
4. **Development builds**
   - `npm start`, asset watcher, block watcher, generated output, and source locations.
5. **HMR and live reload**
   - BrowserSync versus block Fast Refresh, WordPress constants, HTTPS, and ports.
6. **Production builds**
   - Command, expected artifacts, and when to commit build output.
7. **Tests and quality gates**
   - Jest, PHPUnit under `wp-env`, PHPCS/PHPCBF, PHPStan, ESLint, and Stylelint.
   - State which commands run on the host and which run in the container.
8. **Troubleshooting**
   - Docker, port collisions, certificates, missing assets, stale builds, and test-container failures.

## Documentation consolidation

- Keep `docs/asset-building-process.md` as detailed asset-pipeline reference or merge it into this guide.
- Keep `docs/hmr.md` as advanced HMR reference, linked from the main workflow.
- Remove duplicate commands and contradictory environment requirements from README and CONTRIBUTING.

## Acceptance criteria

- [ ] A developer can start WordPress, start watchers, make a sample change, and see it locally using only this guide.
- [ ] The guide documents ports 5890/5891 and configurable HMR ports.
- [ ] Host-versus-container test and lint execution is unambiguous.
- [ ] Every npm command is verified against `package.json`.
- [ ] The documented aggregate test command terminates and does not select a watch task.
- [ ] README and CONTRIBUTING link to this document instead of duplicating the workflow.
