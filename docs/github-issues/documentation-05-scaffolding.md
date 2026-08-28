# [Docs] Document AI-assisted and raw CLI scaffolding

Parent epic: `#TBD`

Suggested labels: `documentation`, `scaffolding`, `developer-experience`

## Problem

The raw scaffold engine defaults do not match this theme's `inc/Modules/<Kind>` structure unless several conventions are supplied manually. AI-assisted scaffolding applies additional testing, implementation, wiring, and quality-gate steps, but the practical tradeoff is not clear enough.

## Scope

Create `docs/scaffolding.md` as the canonical guide for adding functionality after initialization.

## Proposed structure

1. **Choose a workflow**
   - Raw CLI for deterministic, fully specified artifacts.
   - `/scaffold` for intent-driven implementation, tests, wiring, and verification.
2. **Theme-appropriate scaffold kinds**
   - Dynamic block, shortcode, settings page, admin page, and registrable service.
3. **Companion-plugin kinds**
   - CPT, taxonomy, REST controller, cron, CLI command, and persistent roles.
4. **Theme conventions**
   - Namespace, source/test paths, text domain, build directory, and `Main::CLASSES` wiring.
5. **Raw CLI workflow**
   - Discover, dry run, generate, inspect output, wire, complete tests, and run gates.
6. **AI-assisted workflow**
   - Brief, confirmation, TDD loop, wiring consent, implementation, and final report.
7. **Worked examples**
   - One complete example for every theme-appropriate kind.
8. **Troubleshooting**
   - Wrong `includes/` output, missing inputs, remote scaffold failure, duplicate wiring, and build/test prerequisites.

## Developer-experience improvement

Evaluate adding a theme-aware wrapper such as:

```bash
npm run scaffold -- wp/shortcode --tag=reading_time --class=ReadingTime
```

The wrapper should supply the theme namespace, `inc/Modules/<Kind>`, `tests/php`, text domain, block build directory, and wiring target automatically. If a wrapper is not implemented, place a prominent warning above the raw CLI examples that generic defaults create the wrong structure.

## Acceptance criteria

- [ ] The guide contains a decision table comparing CLI and AI behavior.
- [ ] All theme-appropriate and companion-plugin kinds are clearly separated.
- [ ] Raw CLI examples generate files under the correct theme paths.
- [ ] Each worked example includes generation, wiring, tests, and final gates.
- [ ] The `includes/` versus `inc/` default mismatch is prevented or prominently documented.
- [ ] The guide explains what the engine generates and what the developer or AI must still implement.
