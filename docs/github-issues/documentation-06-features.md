# [Docs] Document optional features and removable example sets

Parent epic: `#TBD`

Suggested labels: `documentation`, `features`, `developer-experience`

## Problem

Initialization capabilities are defined in `bin/scaffold.config.js`, but their defaults, files, runtime effects, and lifecycle are scattered across init skills and feature-specific documents. Tailwind documentation currently describes a manual setup that conflicts with the init feature.

## Scope

Create `docs/features.md` as the user-facing capability reference and make feature-specific guides consistent with it.

## Proposed structure

1. **Capability model**
   - Difference between removable example sets and repeatable optional-feature toggles.
2. **Default state table**
   - HMR enabled by default.
   - Tailwind disabled by default.
   - Example sets retained by default.
3. **Example sets**
   - Media-text block extension.
   - Theme settings page.
   - Author-bio shortcode.
   - Button/card components.
   - Page-creation pattern.
4. **Optional features**
   - HMR: files, environment flag, runtime behavior, and links to advanced configuration.
   - Tailwind: files, dependencies, generated token file, runtime enqueue, and links to usage details.
5. **Commands**
   - List, exact feature set, enable, disable, keep examples, and remove examples.
6. **Lifecycle and limitations**
   - What can be toggled later and what is removed permanently.
7. **Adding functionality after init**
   - Link to scaffolding rather than suggesting init can restore removed examples.

## Required corrections

- Replace the manual Tailwind installation path with `npm run init -- --enable=tailwind` as the primary path.
- Align `docs/tailwind.md`, `DEVELOPMENT.md`, the feature template, and webpack behavior around `_tailwind-theme.css`.
- Explain whether enabling a feature updates dependency declarations only or also installs packages.

## Acceptance criteria

- [ ] Every capability in `bin/scaffold.config.js` appears in one table with its key, default, files, and effect.
- [ ] HMR and Tailwind commands match init help output.
- [ ] Tailwind setup instructions match the feature implementation.
- [ ] Example removal is labeled destructive and one-shot.
- [ ] Developers can determine how to enable, disable, verify, and troubleshoot each optional feature.
