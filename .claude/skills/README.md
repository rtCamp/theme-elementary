# AI skills

Skills for AI assistants (Claude Code, Cursor, and any tool that reads the Claude Code skill convention) that drive this theme's setup and the `@rtcamp/wp-tooling` scaffold engine. Each skill is a directory with a `SKILL.md` (frontmatter `name:` + `description:`, portable Markdown body).

| Skill | Invoke | What it does |
|---|---|---|
| [`init/`](init/SKILL.md) | `/init` | Turn this cloned theme into a named theme and manage it afterwards: rename the starter tokens, keep/remove the example sets, toggle optional features (Tailwind, HMR). Drives `npm run init`. |
| [`scaffold/`](scaffold/SKILL.md) | `/scaffold` | Add one feature (dynamic block, shortcode, settings/admin page, service, CI workflow - plus CPT/taxonomy/REST/CLI/cron for a companion plugin) via `npx wp-tooling add`, TDD-first, wiring it into `Main::CLASSES`. |

`init` is theme-specific. `scaffold` tracks `@rtcamp/wp-tooling` and introspects the project, so it stays correct as the layout evolves. [`setup/`](setup/SKILL.md) (`/setup`) is the natural-language bootstrapper: from one brief it applies tooling and chains a sequence of feature scaffolds.

**Copilot parity:** `init` and `scaffold` also exist for GitHub Copilot as prompt files in [`.github/prompts/`](../../.github/prompts/) (`/init`, `/scaffold`), kept consistent with these skills. Shared conventions and the knowledge-graph (graphify) policy live in [`AGENTS.md`](../../AGENTS.md).

## Safety

These skills are opinionated about safety and never: run a package manager or `npm run build` without consent; read, log, or transmit secret values; apply cross-file wiring without showing the diff and getting consent; or commit, push, or open PRs without approval. `init` additionally never runs a destructive setup without confirming the resolved values against a clean working tree.

Two consented exceptions to the package-manager rule: `npm run init` (the theme's own setup script), and the **pilot bootstrap** `init` runs on request (sibling clone of the tooling engine + local `file:` ref + `composer update`/`npm install`). After any code change, the skills refresh the local graph with `graphify update .`.
