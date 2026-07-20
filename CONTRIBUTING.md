# Contributing to Theme Elementary

Thanks for your interest in improving Theme Elementary — a WordPress block
starter theme built on the `rtcamp/wp-framework` package.

## Development setup

Requires the Node version in `.nvmrc` (`nvm use`) and PHP 8.2+.

```bash
nvm use
composer install   # PHP dependencies + tooling (PHPCS, PHPStan)
npm install        # build toolchain
```

## Building assets

```bash
npm start           # watch build (blocks + assets)
npm run build:prod  # production build
```

## Before you open a PR

Run the checks — all must pass:

```bash
npm run lint:all    # PHP (PHPCS) + JS (ESLint) + CSS (Stylelint)
composer phpstan    # static analysis
npm test            # JS + PHP test suites (PHP runs via @wordpress/env)
```

## Pull request checklist

- [ ] `npm run lint:all` and `composer phpstan` pass.
- [ ] `npm test` passes.
- [ ] New/changed behavior is covered by tests.
- [ ] Commits follow [Conventional Commits](https://www.conventionalcommits.org/).

## License

By contributing, you agree that your contributions are licensed under the
project's [GPL-2.0-or-later](LICENSE) license.
