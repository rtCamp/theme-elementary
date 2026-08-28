# [Docs] Separate pilot and maintainer procedures from user documentation

Parent epic: `#TBD`

Suggested labels: `documentation`, `maintainer`, `internal`

## Problem

Pilot dependency overrides, sibling repository checkouts, graph generation, release testing, and internal cleanup checks appear alongside first-time user instructions. Some of those instructions are already stale relative to the v2 package configuration.

## Scope

Create `docs/internal/` for non-user workflows and move or rewrite `docs/internal-testing.md` accordingly.

## Proposed structure

```text
docs/internal/
├── README.md                 # Audience, status, and index
├── pilot-installation.md     # Temporary package and branch procedures
├── release-validation.md     # Fresh-project and upgrade smoke tests
├── cleanup-validation.md     # Downstream cleanup contract
├── dependency-development.md # Sibling wp-framework/wp-tooling workflows
└── knowledge-graph.md        # Maintainer graph generation/update policy
```

## Content requirements

### Pilot installation

- State the exact package state and why the workaround is needed.
- Date or version-bound every temporary instruction.
- Remove token instructions when dependencies resolve from Git refs.

### Release validation

- Test stable Composer installation and branch-clone installation separately.
- Run init interactively and non-interactively.
- Exercise every example-set removal and optional-feature combination.
- Start `wp-env`, build assets, and run documented gates.
- Run a documentation link and command smoke check.

### Cleanup validation

- Verify cleanup execution order.
- Confirm retained AI tooling and documentation references.
- Confirm no deleted file remains referenced by README, AGENTS, Composer scripts, npm scripts, or generated configuration.

### Dependency development

- Document safe sibling checkout and local `file:`/Composer path repository workflows.
- Explain exactly which local-only changes must be reverted without discarding identity changes.

## Acceptance criteria

- [ ] Public onboarding contains no maintainer-only dependency workaround.
- [ ] Every internal page declares its audience and whether the process is temporary.
- [ ] Existing `docs/internal-testing.md` is removed, redirected, or updated so it cannot contradict `.npmrc` and `package.json`.
- [ ] Release validation covers create-project, init, cleanup, local startup, build, tests, and documentation links.
- [ ] Temporary pilot instructions include an explicit removal condition.
- [ ] The internal index is linked from CONTRIBUTING or the maintainer section, not from the primary onboarding path.
