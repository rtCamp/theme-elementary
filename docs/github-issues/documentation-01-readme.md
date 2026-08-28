# [Docs] Make the README the concise documentation entry point

Parent epic: `#TBD`

Suggested labels: `documentation`, `developer-experience`, `theme-elementary-v2`

## Problem

The README mixes product description, setup, development commands, AI tooling, and architecture without establishing one reliable entry path. The Composer path also implies that initialization runs automatically, while the v2 implementation requires a separate init step.

## Scope

Rewrite `README.md` as a short landing page. Move procedural detail into canonical documents and link to them.

## Proposed structure

1. **What Theme Elementary is**
   - Block-theme skeleton built on `rtcamp/wp-framework`.
   - Who should use it and what it does not provide.
2. **Requirements**
   - Link to the full prerequisite matrix in Getting Started.
3. **Get started**
   - Stable/released path.
   - Clearly marked v2 pilot path while the branch is not the default.
   - Five-to-seven commands maximum.
4. **What is included**
   - Initialization and identity replacement.
   - Example-set selection.
   - HMR and optional Tailwind.
   - Asset pipeline, tests, linting, scaffolding, and AI tooling.
5. **Common commands**
   - Init, local environment, watch build, production build, tests, and lint.
6. **Choose your next task**
   - Initialize a theme.
   - Start local development.
   - Add a feature.
   - Understand the architecture.
7. **Documentation index**
8. **Contributing and license**

## Implementation notes

- Do not claim that `composer create-project` invokes the init wizard unless a verified Composer hook does so.
- Avoid running npm installation both implicitly through Composer and explicitly in the documented sequence. Document the actual behavior or decouple the scripts.
- Link directly to asset, HMR, scaffolding, and local-environment documentation.
- Ensure every advertised AI file survives downstream cleanup.

## Acceptance criteria

- [ ] The README contains one canonical golden path.
- [ ] Pilot and stable installation paths are visibly separated.
- [ ] The path includes `npm run init` or explains the equivalent `/init` workflow.
- [ ] The README does not contain obsolete branch or private-package instructions.
- [ ] All links resolve before and after supported cleanup.
- [ ] A new developer can identify the next document for setup, development, scaffolding, and architecture in under one minute.
