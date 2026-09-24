# Theme Elementary

Build a WordPress block theme from this starter theme, then keep the examples and tools your project needs. Start with [Getting Started](getting-started.md).

## Two ways to work

This starter is set up and extended in two ways: **AI skills** (guided — `/init` and `/scaffold` in Claude Code) or the **raw CLI** (manual — `npm run init` and `npx wp-tooling add`).

- **`/init` / `npm run init`** — names the theme and selects which example sets ship. Run once per repo; see [Initialization](initialization.md).
- **`/scaffold` / `npx wp-tooling add`** — adds one feature (a block, shortcode, settings page, service, and so on), wired into `Main::CLASSES`. See [Scaffolding](scaffolding.md).

Both are valid — pick the CLI when you want full control over every input, or the AI skill when you want the wizard to infer conventions from a brief and check its own work.

| I want to… | Read |
| --- | --- |
| Create a theme and see it running | [Getting Started](getting-started.md) |
| Understand init prompts or change options later | [Initialization](initialization.md) |
| Edit source, run checks, and build | [Local development](local-development.md) |
| Find the supplied examples and infrastructure | [Included features](features.md) |
| Generate one feature with CLI or AI | [Scaffolding](scaffolding.md) |
| Extend the theme by hand | [Development guide](../DEVELOPMENT.md) |

## Further reading

- [Asset builds](asset-building-process.md), [live reload](hmr.md), and [Tailwind](tailwind.md) cover theme-specific configuration.
- [Contributing](../CONTRIBUTING.md) and [maintenance](internal/README.md) are for developers maintaining the starter theme itself.

## Shared dependencies

PHP contracts: [wp-framework](https://github.com/rtCamp/wp-framework/blob/main/docs/index.md). Init and scaffold tooling: [wp-tooling](https://github.com/rtCamp/wp-tooling/blob/main/README.md). Runtime telemetry: [wp-devtools](https://github.com/rtCamp/wp-devtools/blob/release/v1.0.0/README.md) (repository access required).

## Command reference

| Task | Command |
| --- | --- |
| Personalize the theme (AI) | `/init <brief>` |
| Personalize the theme (CLI) | `npm run init` |
| Check current identity/feature state | `npm run init -- --list` |
| Generate a feature (AI) | `/scaffold <description>` |
| Preview a feature (CLI) | `npx wp-tooling add wp/<kind> ... --dry-run` |
| Watch and build assets | `npm start` / `npm run build:prod` |
| Run PHP tests | `npm run test:php` |
| Run PHP checks | `composer phpcs && composer phpstan` |
