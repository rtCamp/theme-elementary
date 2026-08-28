# [Epic] Restructure Theme Elementary documentation

Suggested labels: `documentation`, `epic`, `theme-elementary-v2`

## Summary

Reorganize the Theme Elementary documentation around one clear developer journey: acquire the skeleton, initialize it, start local development, add features, and maintain the resulting theme.

The current documentation contains useful detail, but setup instructions are spread across the README, Quick Start, development guides, AI skills, and pilot notes. Some instructions conflict with the implementation on `theme-elementary-v2`, including the branch name, package-resolution flow, Tailwind setup, cleanup behavior, and the responsibilities of Composer, `npm run init`, `/init`, `/setup`, and `/scaffold`.

## Goals

- Give first-time users one verified setup path with visible success checks.
- Separate stable user documentation from pilot and maintainer procedures.
- Make AI-assisted and manual workflows equivalent and easy to compare.
- Document the functionality already provided by the skeleton.
- Make each command, prerequisite, and cross-document link testable.

## Proposed documentation map

| Document | Purpose | Issue draft |
|---|---|---|
| `README.md` | Product overview, golden path, feature summary, documentation index | [README](documentation-01-readme.md) |
| `docs/getting-started.md` | Prerequisites and end-to-end setup | [Getting started](documentation-02-getting-started.md) |
| `docs/initialization.md` | Initialization entry points, cleanup, and rerun behavior | [Initialization](documentation-03-initialization.md) |
| `docs/local-development.md` | `wp-env`, asset builds, HMR, tests, and troubleshooting | [Local development](documentation-04-local-development.md) |
| `docs/scaffolding.md` | AI and CLI scaffolding, supported kinds, paths, and wiring | [Scaffolding](documentation-05-scaffolding.md) |
| `docs/features.md` | Optional features and removable example sets | [Features](documentation-06-features.md) |
| `DEVELOPMENT.md` | Architecture and manual extension guidance | [Development guide](documentation-07-development-guide.md) |
| `docs/internal/` | Pilot, release, and maintainer-only procedures | [Internal documentation](documentation-08-internal-docs.md) |

## Sub-issues

- [ ] Create the README as the concise documentation entry point.
- [ ] Add a verified Getting Started guide.
- [ ] Document initialization and cleanup behavior.
- [ ] Add a complete local-development workflow.
- [ ] Document AI-assisted and raw CLI scaffolding.
- [ ] Document optional features and example sets.
- [ ] Refocus the Development Guide on architecture and manual extension.
- [ ] Move pilot and maintainer procedures into an internal documentation area.

## Shared requirements

Every sub-issue should follow these rules:

- Use `theme-elementary-v2` behavior as the source of truth until v2 becomes the default release.
- Test every published command in a fresh clone or disposable directory.
- Avoid duplicating detailed instructions; choose one canonical page and link to it.
- Clearly label stable, pilot, optional, destructive, one-time, and maintainer-only actions.
- Keep terminology consistent: skeleton, initialization, example set, optional feature, scaffold, framework, and tooling.
- Include expected output or a success check at major workflow boundaries.
- Keep relative links valid after downstream cleanup.
- Remove or update obsolete instructions as part of the same pull request.

## Completion criteria

- All eight sub-issues are complete.
- A first-time developer can reach a running local WordPress site using only the README and Getting Started guide.
- Manual and AI-assisted paths produce the same project structure.
- No public guide requires an unpublished branch, token, or sibling checkout unless explicitly marked as a pilot procedure.
- Cleanup does not leave documentation pointing to deleted files.
- Documentation link and command smoke checks run in CI.

## Out of scope

- Redesigning the framework or scaffold engine APIs.
- Adding new scaffold kinds.
- Changing the theme architecture except where required to make a documented workflow accurate.
