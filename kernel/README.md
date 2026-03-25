# Kernel

The `kernel` directory is the foundational layer of the Elementary Theme. It provides a set of base classes, abstract contracts, and reusable traits that the rest of the theme is built upon.

> **Important:** Files within this directory should never be modified directly. They are designed to be extended, not altered. If you think something can be added here and can be beneficial for wider use cases, please update it in the upstream repository.

---

## Directory Structure

```
kernel/
├── Abstracts/
│   ├── Abstract_Asset_Loader.php
│   └── Abstract_Block_Extension.php
├── Traits/
│   └── Singleton.php
└── AutoLoaderTrait.php
```

---

## Overview

### `Abstracts/`

Contains abstract base classes that define the contracts for core theme subsystems. Any concrete implementation must extend these classes and fulfill their interface.

| File | Description |
|---|---|
| `Abstract_Asset_Loader.php` | Base class for registering and enqueueing theme assets. Handles scripts and stylesheets. |
| `Abstract_Block_Extension.php` | Base class for extending Gutenberg core blocks via render filters. |

---

### `Traits/`

Contains reusable traits that can be composed into any class across the theme.

| File | Description |
|---|---|
| `Singleton.php` | Enforces the singleton pattern, ensuring a class is only instantiated once. |

---

### `AutoLoaderTrait.php`

A trait that wraps the Composer autoloader with graceful failure handling. If the autoloader is missing, it surfaces an admin notice rather than causing a fatal error.

---

## Extending the Kernel

All classes in `inc/` that require base functionality should extend the appropriate kernel abstract rather than implementing the functionality from scratch.

**Asset loading:**
```php
use Elementary_Theme\Kernel\Abstracts\Abstract_Asset_Loader;

class Assets extends Abstract_Asset_Loader {
    // ...
}
```

**Block extensions:**
```php
use Elementary_Theme\Kernel\Abstracts\Abstract_Block_Extension;

class My_Block_Extension extends Abstract_Block_Extension {
    // ...
}
```

**Singleton:**
```php
use Elementary_Theme\Kernel\Traits\Singleton;

class My_Class {
    use Singleton;
    // ...
}
```

---

## Design Principles

- **Immutability** — Kernel files define contracts, not implementations. They should remain stable across all themes built on this base.
- **Extensibility** — Every kernel class is designed to be extended. Concrete behaviour lives in `inc/`, never in `kernel/`.
- **Single Responsibility** — Each file in the kernel is responsible for one concern only.
```
