# [Docs] Document initialization, cleanup, and rerun behavior

Parent epic: `#TBD`

Suggested labels: `documentation`, `initialization`, `developer-experience`

## Problem

Composer setup, `npm run init`, `/init`, and `/setup` are described as overlapping setup mechanisms. Cleanup can also remove files that are still referenced by the resulting project.

## Scope

Create `docs/initialization.md` as the canonical description of project personalization and capability selection.

## Proposed structure

1. **Where initialization starts and ends**
   - Acquisition and dependency installation are prerequisites.
   - Initialization begins with an unpersonalized skeleton and ends with a named theme.
2. **Entry-point matrix**
   - `npm run init`: deterministic CLI/wizard.
   - `/init`: AI-guided interface over init plus optional scaffold handoff.
   - `/setup`: generic tooling bootstrap; clarify whether it is supported for this skeleton.
3. **Identity fields**
   - Name, version, namespace, package, text domain, function prefix, constant prefix, and CSS prefix.
4. **Example-set selection**
   - Defaults, available sets, coupled files, and one-shot removal behavior.
5. **Optional feature selection**
   - HMR and Tailwind defaults and later manage-mode toggles.
6. **Cleanup behavior**
   - Files removed, files retained, execution order, and downstream consequences.
7. **Manage and rerun behavior**
   - Safe later actions versus actions that cannot be repeated safely.
8. **Recovery**
   - Clean-tree requirement, preview/list commands, and how to recover from a failed init.
9. **Command reference**
   - Every supported flag with examples.

## Required implementation verification

- Run the complete init and cleanup flow in a fresh clone.
- Ensure cleanup scripts execute before their containing directory can be removed.
- Decide whether `.github` AI instructions and prompts are downstream functionality or skeleton-only infrastructure.
- Update `AGENTS.md`, README, and other references when cleanup removes a linked file.

## Acceptance criteria

- [ ] The guide defines one lifecycle and the role of every initialization entry point.
- [ ] The exact cleanup target list and ordering are documented and tested.
- [ ] Init leaves no dangling documentation links or class registrations.
- [ ] One-shot example removal is clearly distinguished from repeatable feature toggles.
- [ ] Destructive commands include a clean-tree warning and a success/recovery checkpoint.
- [ ] Help output and documentation list the same flags and defaults.
