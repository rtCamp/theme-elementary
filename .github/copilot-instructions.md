# Copilot instructions — Theme Elementary

A custom WordPress **block theme** (PHP 8.2+) built on `rtcamp/wp-framework` (`rtCamp\WPFramework`, installed in gitignored `vendor/`: not visible at review).

Detailed, path-scoped rules live in `.github/instructions/`:
- `framework-php.instructions.md`: all `**/*.php`: framework architecture, security, testing, review flags.
- `structure.instructions.md`: theme layout and wiring.

## Stack

- PHP 8.2+, Composer, PSR-4 autoload (namespace === directory, filename === class).
- PHPUnit + `wp-phpunit` in `tests/php/` (mirrors `inc/`).
- PHPCS (WordPress-Core/Extra/Docs + VIPCS) and PHPStan: zero errors before merge.
- Block theme: `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/`.

## Universal rules

- **TDD**: write the failing test first, then the implementation. Never ship code without a test.
- **Do not default to Singleton.** Hook WordPress through the framework `Loader` + `Registrable`; use `Shareable` only when an instance must be retrieved later. Full decision tree in `framework-php.instructions.md`.
- Prefer official WordPress / `@wordpress/*` APIs and block-theme mechanisms (`theme.json`, patterns, template parts) over custom PHP.
- Never modify WordPress core or anything under `vendor/`; extend via actions and filters.

## Commands

- `vendor/bin/phpunit -c phpunit.xml.dist` · `composer phpcs` · `composer phpcs:fix` · `composer phpstan`.

## Review conduct

These tune *how* you comment; the *what* lives in the path-scoped files. Balance matters: catch every real issue, but don't drown it in noise.

- **One comment per distinct issue, and never more than one per comment.** Every distinct problem gets its OWN separate comment at its own line, even when several issues sit on the same or adjacent lines (e.g. an unauthenticated `permission_callback`, raw `$_POST` use, and SQL injection in one method are THREE comments, not one). Do not merge unrelated findings. Conversely, when a *single* root cause spans several lines (e.g. the `Singleton` trait plus its `get_instance()` bootstrap), post it once at the clearest line and name the related lines; don't repeat the same finding.
- **Always post the missing-tests (TDD) finding** when a new/changed feature or class ships without a matching test in `tests/php/`. This is mandatory and exempt from dedupe, trimming, and any comment-budget; never drop it in favour of other findings.
- **Order the rest by impact:** security → architecture/contract (`Singleton`/`Shareable`/`Loader`/PSR-4/abstracts) → WordPress correctness → style. Lead with what breaks things.
- **Don't spend comments on what PHPCS/PHPStan already enforce.** A bare "add a `void` return type" or "add visibility" is noise; the linters catch it. Only raise a typing issue when it changes behavior or hides a real bug. If a non-linter-covered style note is worth raising, give it its own comment too.
- **On a correct, deliberate implementation, don't manufacture findings.** An idiomatic, defensible choice is not a bug. Prefer **zero comments** over low-value ones. A clean PR comes back clean. (A genuine correctness/security observation on otherwise-good code is still welcome; a stylistic preference is not.)
