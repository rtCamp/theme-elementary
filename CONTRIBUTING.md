# Contributing to Theme Elementary

Thanks for your interest in improving Theme Elementary — a WordPress block
starter theme built on the `rtcamp/wp-framework` package.

## Development setup

Use [Getting started](docs/getting-started.md) for dependency installation —
it installs PHP dependencies and tooling like PHPCS/PHPStan via Composer, and
the build toolchain via npm — and [Local development](docs/local-development.md)
for the daily environment, build, and verification workflow. The project
requires the Node version in `.nvmrc` and PHP 8.2+.

For release validation, local dependency work, and documentation publishing, use
the [maintainer guidance](docs/internal/README.md).

## Building assets

```bash
npm start           # watch build (assets + blocks)
npm run build:prod  # production build
```

See [Local development](docs/local-development.md#edit-source-and-see-the-result)
for watch-build details and [the delivery build](docs/local-development.md#build-for-delivery)
for production output.

## Before you open a PR

Run the relevant focused tests and lint commands in
[Local development](docs/local-development.md#check-a-change); all must pass.

## Pull request checklist

- [ ] The relevant focused tests and lint commands pass.
- [ ] New/changed behavior is covered by tests.
- [ ] Commits follow [Conventional Commits](https://www.conventionalcommits.org/).

## License

By contributing, you agree that your contributions are licensed under the
project's [GPL-2.0-or-later](LICENSE) license.
