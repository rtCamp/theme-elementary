# [Docs] Add a verified end-to-end Getting Started guide

Parent epic: `#TBD`

Suggested labels: `documentation`, `onboarding`, `developer-experience`

## Problem

The current Quick Start mixes pilot dependency workarounds, AI instructions, raw CLI examples, and setup details. It does not take the developer through a complete local WordPress startup and verification.

## Scope

Replace or supersede `docs/quick-start-guide.md` with `docs/getting-started.md`, focused only on reaching a working local theme.

## Proposed structure

1. **Outcome**
   - What will be running when the guide is complete.
2. **Prerequisites**
   - Git, Node from `.nvmrc`, npm, PHP 8.2+, Composer, and Docker.
   - Verification commands and supported versions.
3. **Acquire the skeleton**
   - Stable Composer path.
   - Temporary v2 branch-clone path, clearly marked.
4. **Install dependencies**
   - Explain whether Composer invokes npm or make both steps explicit.
5. **Initialize the theme**
   - Guided CLI path.
   - Equivalent AI path as an alternative, not an additional required step.
6. **Start WordPress locally**
   - Start `wp-env`, explain configured ports, and confirm the site is reachable.
7. **Build assets**
   - Watch and production modes.
8. **Verify success**
   - Theme identity, activation, frontend, editor, asset output, and a basic test command.
9. **Next steps**
   - Local development, scaffolding, features, and architecture.
10. **Troubleshooting**
    - Docker unavailable, occupied ports, install failures, and missing assets.

## Checkpoints

Add a short success check after each destructive or time-consuming phase:

- Dependencies installed.
- Identity replacement completed.
- Example selection completed.
- WordPress started.
- Theme loaded without PHP or browser-console errors.
- Assets rebuilt after a sample edit.

## Acceptance criteria

- [ ] The guide is tested from a fresh clone in a disposable directory.
- [ ] No obsolete branch name, token setup, or registry workaround appears in the standard path.
- [ ] AI and manual initialization are presented as alternatives.
- [ ] Local WordPress startup and verification are included.
- [ ] Every major step includes expected output or a success check.
- [ ] Pilot-only material links to `docs/internal/` instead of interrupting the main flow.
