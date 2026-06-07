# AGENTS.md — Theme Elementary

Tool-agnostic brief for AI coding agents (Claude Code, Copilot coding agent, Codex). A custom WordPress **block theme** built on `rtcamp/wp-framework` (in `vendor/`).

## Authoritative rules

The review rules ARE the coding rules: the same files Copilot reviews against. Follow them when writing code; they hold the full detail:

- `.github/instructions/framework-php.instructions.md`: framework architecture, security, testing, and the do/don't flags. (Shipped from `rtcamp/wp-framework`; the single source of truth.)
- `.github/instructions/structure.instructions.md`: theme layout and wiring.
- `.github/copilot-instructions.md`: overview + conventions.

## Key principles (full detail in the files above)

- **TDD**: write the failing PHPUnit test first (`tests/php/` mirrors `inc/`), then code.
- **Don't default to Singleton.** Hook WordPress via the framework `Loader` + `Registrable`; use `Shareable` only when an instance must be retrieved later via `get_shared()`. `Singleton` is for `Main` only.
- **Extend the framework abstracts** (e.g. an options page → `AbstractSettingsPage`) instead of hand-rolling registration.
- **Prefer `theme.json` / block patterns / template parts** over hardcoded markup or styling in PHP.
- **WordPress security**: escape/sanitize, nonce + `current_user_can()` before mutations, `$wpdb->prepare()`, a real REST `permission_callback`.
- `declare( strict_types = 1 );`, full types, `@package`/`@since`, `static::` not `self::`, PSR-4 (namespace === dir).

## Structure

`inc/` (PSR-4 `rtCamp\Theme\Elementary\`): `Main.php` (boot), `Core/` (`ThemeSetup`/`Menu`/`Assets`, implement `Registrable`), `Modules/`. `Main::CLASSES` lists classes directly (no `AbstractModule` grouping yet). Block config in `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/`. Text domain `elementary-theme`. `tests/php/` mirrors `inc/`; `inc/Modules/` classes are scaffolding; delete unused.

To add a feature: write the test, implement `Registrable` (or extend an abstract), add its `::class` to `Main::CLASSES`.
