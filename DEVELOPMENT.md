# Development Guide

## PSR-4 Namespace Convention

All namespaced PHP classes live under `inc/` using the `rtCamp\Theme\Elementary\` root namespace.
The file path must map to the namespace and class name.

Examples:

- `inc/Main.php` → `rtCamp\Theme\Elementary\Main`
- `inc/Core/Assets.php` → `rtCamp\Theme\Elementary\Core\Assets`
- `inc/BlockExtensions/MediaTextInteractive.php` → `rtCamp\Theme\Elementary\BlockExtensions\MediaTextInteractive`
- `inc/Framework/Traits/Singleton.php` → `rtCamp\Theme\Elementary\Framework\Traits\Singleton`

## Two-layer model

The repository is split into two layers:

- `inc/Framework/` — upstream-owned framework code. Do not modify in downstream projects.
- `inc/` (outside `Framework/`) — project-specific implementation and customizations.

### `inc/Framework/`

This is the base layer. It should contain only reusable traits, interfaces, and low-level utilities.
Treat it like vendored code. If you need to change behavior, extend the framework class in `inc/` instead.

### `inc/`

This is the application layer for this theme. Add new feature classes, hooks, and theme-specific behavior here.

## Adding a new class

1. Create a new PHP file under `inc/` using PascalCase file names.
2. Use the matching namespace path.
3. Define the class name to match the filename exactly.
4. If the class belongs to a feature group, create a subdirectory and namespace that group.

Example:

`inc/Example/Feature.php`

```php
namespace rtCamp\Theme\Elementary\Example;

class Feature {
    // ...
}
```

## Existing non-namespaced file

`inc/helpers/custom-functions.php` is intentionally not namespaced and remains loaded through Composer `files` autoload.

## Running autoload generation

After moving or adding namespaced classes, regenerate Composer autoload files:

```bash
composer dump-autoload
```

## Notes

- Do not use `classmap` autoloading for namespaced classes.
- Keep the `rtCamp\Theme\Elementary\` PSR-4 root aligned with `inc/`.
