<h1 align="center">Theme Elementary</h1>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg" alt="License: GPL-2.0-or-later"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/WordPress-block%20theme-21759b.svg" alt="WordPress block theme">
</p>

<p align="center">
  A starter <a href="https://developer.wordpress.org/block-editor/how-to-guides/themes/block-theme-overview/">block theme</a>
  that gives you a quick head start on new block-based themes, with a bunch of
  developer-friendly features built in.
</p>

<p align="center">
  <img src="https://user-images.githubusercontent.com/59014930/187202051-df015d4a-f885-40cb-9fc9-c13991d3216d.png" alt="Theme Elementary preview" width="100%">
</p>

---

Reusable scaffolding (singleton, autoloader, asset loader, template loader, and
abstract base classes) ships separately as the
[`rtcamp/wp-framework`](https://github.com/rtCamp/wp-framework) Composer package
and is loaded from `vendor/`.

> **Working on this theme?** See [DEVELOPMENT.md](DEVELOPMENT.md) for the
> architecture overview, the module pattern, and how to add new classes.
> [CONTRIBUTING.md](CONTRIBUTING.md) covers the dev setup and PR flow.

## Get started

**Recommended** — scaffold a new project (runs `composer install && npm install`
and a setup wizard that does the search-replace):

```bash
composer create-project rtcamp/elementary [folder-name]
```

**Manual** — clone, then install and let the wizard run:

```bash
git clone <repo-url> && cd <clone-dir>
composer install
npm install
```

Use the Node version in `.nvmrc` (`nvm use`). That's it — you're ready to build
your block theme. ✨

## Development

```bash
npm start            # watch build
npm run build:prod   # production build

npm run lint:js      # + lint:css, lint:php (PHPCS)
npm run lint:js:fix  # + lint:css:fix, lint:php:fix (PHPCBF)

npm run test         # all tests; also test:js, test:js:watch, test:php
```

## Folder structure

```
functions.php               # PHP entry point
inc/                        # project PHP (PSR-4 root)
├── Autoloader.php          # wraps vendor/autoload.php with graceful failure
├── Main.php                # theme bootstrap — loads services
├── Helpers/                # stateless static utilities
├── Core/                   # theme-wide infra — assets, menus, theme setup
└── Modules/                # feature areas
    ├── BlockExtensions/    # block render filters and integrations
    └── Settings/           # admin settings pages (extend AbstractSettingsPage)
src/{css,js,fonts,images}/  # frontend sources → assets/build/
parts/ patterns/ templates/ # block parts, patterns, templates
theme.json  style.css       # theme config
tests/{js,php}/             # JS & PHP tests
vendor/rtcamp/wp-framework/ # framework (Composer-managed; do not modify)
```

## License

[GPL-2.0-or-later](LICENSE)

<p align="center">
  <a href="https://rtcamp.com"><img src="https://n8e0ka87m9.gdcdn.us/kfnbt046p8/GitHub_Banner.webp" alt="rtCamp — high-performance enterprise WordPress" width="100%"></a>
</p>
