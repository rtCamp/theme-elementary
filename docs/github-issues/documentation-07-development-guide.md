# [Docs] Refocus the Development Guide on architecture and manual extension

Parent epic: `#TBD`

Suggested labels: `documentation`, `architecture`, `developer-experience`

## Problem

`DEVELOPMENT.md` explains the PHP framework boundary and class registration, but it does not give developers a decision framework for block-theme work. It can therefore imply that PHP class scaffolding is the default solution even when `theme.json`, a pattern, template part, block style, or client-side block is more appropriate.

## Scope

Revise `DEVELOPMENT.md` into the architecture and manual-extension reference. Keep onboarding, local commands, and feature toggles in their dedicated guides.

## Proposed structure

1. **Architecture overview**
   - Theme-specific code versus `vendor/rtcamp/wp-framework`.
2. **Choose the correct extension point**
   - `theme.json` token/style.
   - Template or template part.
   - Block pattern.
   - Block style or core-block extension.
   - Custom block.
   - PHP service/module.
   - Companion plugin.
3. **PHP structure and PSR-4**
   - `inc/Core`, `inc/Modules`, helpers, tests, and `Main::CLASSES`.
4. **Framework lifecycle**
   - `Registrable`, conditional registration, sharing, and available abstract classes.
5. **Adding a PHP class manually**
   - Test first, implement, wire, dump autoload, and run gates.
6. **Block-theme development**
   - `theme.json`, patterns, templates, parts, styles, blocks, and asset locations.
7. **Security and quality standards**
8. **Architecture examples**
   - Link to small, maintained examples rather than duplicating scaffold commands.
9. **Framework reference**
   - Link to version-compatible framework documentation.

## Acceptance criteria

- [ ] The guide includes a clear decision tree for common theme changes.
- [ ] Theme presentation and persistent application functionality are separated.
- [ ] Manual PHP guidance matches the v2 namespace and wiring model.
- [ ] Tailwind architecture matches the feature implementation and feature guide.
- [ ] Framework abstracts are linked to version-compatible documentation.
- [ ] Setup, local-environment, and scaffold procedures are linked rather than duplicated.
