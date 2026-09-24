# AI skills

Skills for AI assistants (Claude Code, Cursor, and any tool that reads the Claude Code skill convention) that drive this theme's setup and the `@rtcamp/wp-tooling` scaffold engine. Each skill is a directory with a `SKILL.md` (frontmatter `name:` + `description:`, portable Markdown body).

| Skill | Invoke | What it does |
|---|---|---|
| [`init/`](init/SKILL.md) | `/init` | Turn this cloned theme into a named theme and manage it afterwards: rename the starter tokens, keep/remove the example sets, toggle optional features (Tailwind, HMR, Dev Tools). Drives `npm run init`. |
| [`scaffold/`](scaffold/SKILL.md) | `/scaffold` | Add one feature (dynamic block, shortcode, settings/admin page, service, CI workflow - plus CPT/taxonomy/REST/CLI/cron for a companion plugin) via `npx wp-tooling add`, TDD-first, wiring it into `Main::CLASSES`. |

`init` is theme-specific. `scaffold` tracks `@rtcamp/wp-tooling` and introspects the project, so it stays correct as the layout evolves. [`setup/`](setup/SKILL.md) (`/setup`) is the generic natural-language tooling bootstrapper for empty/existing projects. Use `/init` to personalize this skeleton; `/setup` does not replace its identity and capability flow.

**Copilot parity:** `init` and `scaffold` also exist for GitHub Copilot as prompt files in [the maintained Copilot prompts](https://github.com/rtCamp/theme-elementary/tree/theme-elementary-v2/.github/prompts) (`/init`, `/scaffold`), kept consistent with these skills. Shared conventions and the knowledge-graph (graphify) policy live in [`AGENTS.md`](../../AGENTS.md).

## Safety

These skills are opinionated about safety and never: run a package manager or `npm run build` without consent; read, log, or transmit secret values; apply cross-file wiring without showing the diff and getting consent; or commit, push, or open PRs. `init` additionally never runs a destructive setup without confirming the resolved values against a clean working tree.

Dependency installation and `npm run init` require consent. The init skill uses the declared dependencies; Composer's install hook already runs npm. Sibling dependency checkouts are maintainer work. After file changes, refresh the local graph with `graphify update .`.

Cleanup removes the Copilot-specific files under `.github` (`copilot-instructions.md`, `prompts/`, `instructions/`); issue templates, the PR template, `dependabot.yml`, `release.yml`, and workflows stay. `sync-ai` restores only generated framework instructions. The Claude skills remain. Review these removals before personalizing a clone.
