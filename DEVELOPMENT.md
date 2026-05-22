# Development Guide

## Architecture overview

The theme is split into two layers:

- **`vendor/rtcamp/wp-framework/`** — The upstream framework, installed as a Composer dependency. Provides reusable scaffolding (`Singleton`, `Loader`, `Container`, `AssetLoaderTrait`, `TemplateLoaderTrait`) and abstract base classes (`AbstractSettingsPage`, `AbstractPostType`, etc.). **Do not modify.** Changes belong in the framework repository.
- **`inc/`** — All theme-specific code. Extends framework abstracts, registers theme services, and bootstraps the theme.

The `vendor/` boundary enforces the rule by convention: editing files there gets blown away on every `composer install`.

## PSR-4 namespace convention

Single PSR-4 root, declared in `composer.json`:

```json
"autoload": {
    "psr-4": {
        "rtCamp\\Theme\\Elementary\\": "inc/"
    }
}
```

Directory segments map 1:1 to namespace segments. Files are PascalCase.

| Namespace                                                              | File                                                  |
|------------------------------------------------------------------------|-------------------------------------------------------|
| `rtCamp\Theme\Elementary\Main`                                         | `inc/Main.php`                                        |
| `rtCamp\Theme\Elementary\Autoloader`                                   | `inc/Autoloader.php`                                  |
| `rtCamp\Theme\Elementary\Core\Assets`                                  | `inc/Core/Assets.php`                                 |
| `rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive` | `inc/Modules/BlockExtensions/MediaTextInteractive.php`|
| `rtCamp\Theme\Elementary\Modules\Settings\ThemeOptions`                | `inc/Modules/Settings/ThemeOptions.php`               |
| `rtCamp\Theme\Elementary\Helpers\Util`                                 | `inc/Helpers/Util.php`                                |

## Directory layout

```
inc/
├── Autoloader.php              # Wraps vendor/autoload.php with graceful failure
├── Main.php                    # Theme bootstrap — loads services
├── Helpers/                    # Stateless static utility classes (final, private __construct)
│   └── Util.php                # General-purpose helpers (add static methods as needed)
├── Core/                       # Theme-wide infrastructure
│   └── Assets.php              # Asset registration (uses AssetLoaderTrait)
└── Modules/                    # Feature areas
    ├── BlockExtensions/        # Block render filters and integrations
    │   └── MediaTextInteractive.php
    └── Settings/               # Admin settings pages (extend AbstractSettingsPage)
        └── ThemeOptions.php
```

## Helpers

`inc/Helpers/` is the home for stateless utility classes — `final`, `private __construct()`, static methods only. Today it holds one class, `Util`, kept as a placeholder for theme-wide helpers that don't earn their own dedicated class. Add siblings (e.g. `Str`, `Cache`, `Url`) as cross-cutting helpers accumulate, rather than letting `Util` grow into a grab-bag.

## Picking a base

| Feature                                  | Extends / implements                |
|------------------------------------------|-------------------------------------|
| Settings page                            | `AbstractSettingsPage`              |
| Admin (non-settings) page                | `AbstractAdminPage`                 |
| Dynamic block (server-side render)       | `AbstractBlock`                     |
| REST controller                          | `AbstractRESTController`            |
| Shortcode                                | `AbstractShortcode`                 |
| Anything else that just wires hooks      | `Registrable` interface             |
| Same, but registration is conditional    | `ConditionallyRegistrable` interface|

## Adding a new class

1. Pick the right abstract or interface from the table above.
2. Drop the file in the matching `inc/Modules/<Area>/` directory (or `inc/Core/` if it's theme-wide infrastructure).
3. Register it in `Main::__construct()`'s `$this->load( [ … ] )` call.
4. Run `composer dump-autoload`.

Example:

```php
// inc/Modules/Example/Feature.php
namespace rtCamp\Theme\Elementary\Modules\Example;

use rtCamp\WPFramework\Contracts\Interfaces\Registrable;

final class Feature implements Registrable {
    public function register_hooks(): void {
        add_action( 'init', [ $this, 'do_something' ] );
    }

    public function do_something(): void {
        // ...
    }
}
```

Then in `Main::__construct()`:

```php
$this->load( [
    Assets::class,
    MediaTextInteractive::class,
    ThemeOptions::class,
    \rtCamp\Theme\Elementary\Modules\Example\Feature::class,
] );
```

## Conditional registration

A class can opt out of registration at runtime by implementing `ConditionallyRegistrable` instead of `Registrable`:

```php
final class DevToolbarExtension implements ConditionallyRegistrable {
    public function can_register(): bool {
        return defined( 'WP_DEBUG' ) && WP_DEBUG;
    }

    public function register_hooks(): void {
        // Wire dev-only hooks here.
    }
}
```

The `Loader` calls `can_register()` first and skips `register_hooks()` when it returns false.

## Running Composer

```bash
# First-time setup
composer install

# After adding, renaming, or moving a class
composer dump-autoload
```

If `vendor/autoload.php` is missing at runtime, the theme shows an admin notice instead of fataling — see `inc/Autoloader.php` and `AutoloaderTrait` in the framework.
