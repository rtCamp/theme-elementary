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

This is a starter WordPress block theme. It provides the
structure, examples, asset pipeline, checks, and optional development features
needed for building a new block theme.

This theme uses [`rtcamp/wp-framework`](https://github.com/rtCamp/wp-framework)
as a runtime dependency and ships with [`@rtcamp/wp-tooling`](https://github.com/rtCamp/wp-tooling)
for streamlined project initialization and feature scaffolding.

> **Working on this theme?** See [DEVELOPMENT.md](DEVELOPMENT.md) for the
> architecture overview, the module pattern, and how to add new classes.
> [CONTRIBUTING.md](CONTRIBUTING.md) covers the dev setup and PR flow.

## Get started

Start with the [Getting Started guide](docs/getting-started.md) — a short,
concrete walkthrough covering prerequisites, acquiring the starter, installing
dependencies, personalizing the theme, and seeing it running in local
WordPress.

> **Current v2 path:** The verified guide follows the `theme-elementary-v2`
> branch.

## What is included

Initialization personalizes the theme and lets you keep or remove the supplied
examples. The asset pipeline builds CSS, JavaScript, and blocks; HMR, Tailwind,
and Dev Tools are optional. Linting, static analysis, tests, and AI-assisted
setup and scaffolding are included for project development.

See [Included features](docs/features.md) for the available examples and options.

## Choose your next task

Once the theme is running, here's where to go next:

| I want to…                                          | Read                                              |
| --------------------------------------------------- | ------------------------------------------------- |
| Initialize or manage a theme                        | [Initialization](docs/initialization.md)          |
| Run WordPress locally, build, or check a change     | [Local development](docs/local-development.md)    |
| Explore the supplied examples and optional features | [Included features](docs/features.md)             |
| Generate a feature                                  | [Scaffolding](docs/scaffolding.md)                |
| Extend the theme by hand                            | [Development guide](DEVELOPMENT.md)               |

## AI tooling

This theme ships AI-assisted setup and feature scaffolding, kept in step across assistants:

- **Claude Code:** retained skills in [`.claude/skills/`](.claude/skills/) — `/init`, `/scaffold`, and `/setup`.
- **GitHub Copilot:** `/init` and `/scaffold` prompts are available while setting up this source repository.
  - Initialization removes the Copilot-specific files under `.github` (`copilot-instructions.md`, `prompts/`, `instructions/`); issue templates, the PR template, and workflows stay.
- A committed knowledge graph in [`graphify-out/`](graphify-out/) lets AI
  assistants query the codebase's structure instead of reading it all; see
  [docs/internal/knowledge-graph.md](docs/internal/knowledge-graph.md) for how
  it's built and kept current.

Shared conventions for all assistants live in [AGENTS.md](AGENTS.md).

## License

[GPL-2.0-or-later](LICENSE)

<p align="center">
  <a href="https://rtcamp.com"><img src="https://n8e0ka87m9.gdcdn.us/kfnbt046p8/GitHub_Banner.webp" alt="rtCamp — high-performance enterprise WordPress" width="100%"></a>
</p>
