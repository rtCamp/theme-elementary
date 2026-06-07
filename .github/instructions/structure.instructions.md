---
applyTo: "inc/**"
description: "Theme structure. Merges with framework-php.instructions.md and copilot-instructions.md."
---

# Theme structure

Block theme. Namespace `rtCamp\Theme\Elementary\` → `inc/`; tests `rtCamp\Theme\Elementary\Tests\` → `tests/php/`. Text domain `elementary-theme`. Entry: `functions.php` → `Autoloader` → `Main::get_instance()`. Block config in `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/`.

Flatter than a plugin: `Main::CLASSES` lists Core/Module classes directly (no `AbstractModule` grouping yet; add one only when a domain grows several related classes). `inc/Core/` (`ThemeSetup`, `Menu`, `Assets`) implement `Registrable` directly; modules in `inc/Modules/`.

**Scaffolding:** the `inc/Modules/` classes are demos shipped to show the pattern. A real project removes unused ones and adds its own per requirements; do not assume a specific module file exists, nor flag one as "missing". `inc/Core/` infra is real and stays. Framework abstracts always exist in `vendor/`; which you extend is requirement-driven.

To add a feature: create the class implementing `Registrable` (or extending a framework abstract, e.g. an options page → `AbstractSettingsPage`), then add its `::class` to `Main::CLASSES`.

## Flag

- 🚩 New `Registrable` class not added to `Main::CLASSES` → it never loads.
- 🚩 Markup/styling hardcoded in PHP where `theme.json` / a block pattern / template part belongs.
- 🚩 Options page or other registration hand-rolled where a framework `Abstract*` fits.
