# CLAUDE.md

Read **[AGENTS.md](AGENTS.md)**: it is the source of truth for this project's conventions, shared across all AI tools (Claude Code, Copilot, Codex). The detailed, path-scoped rules it points to live in [`.github/instructions/`](.github/instructions/).

Claude Code skills live in [`.claude/skills/`](.claude/skills/):
- `/init` - turn this cloned theme into a named theme, and manage its identity, features, and example sets (drives `npm run init`).
- `/scaffold` - add one feature (dynamic block, shortcode, settings/admin page, service, ...) via `npx wp-tooling add`, TDD-first, wiring it into `Main::CLASSES`.
- `/setup` - bootstrap tooling + a sequence of feature scaffolds from one natural-language brief.

## Knowledge graph (Graphify)

A knowledge graph of this theme (optionally extended to `wp-framework` and `wp-tooling`) is committed at [`graphify-out/graph.json`](graphify-out/graph.json) (human summary in [`graphify-out/GRAPH_REPORT.md`](graphify-out/GRAPH_REPORT.md)), built with the official [Graphify](https://graphify.net) utility.

Before reading source files to answer questions about architecture, symbols, call paths, or how the theme wires together, query the graph first to save tokens:

- Use the `/graphify` skill, or the CLI: `graphify query "<question>"`, `graphify path "A" "B"`, `graphify explain "<symbol>"`, `graphify affected "<symbol>"`.
- The graph is structural (tree-sitter, local, no API key). Read files only when the graph does not answer.

Regenerate after large changes; recipe in [docs/knowledge-graph.md](docs/knowledge-graph.md).

Claude-specific notes:
- _(none currently; keep Claude overrides here if they ever diverge from AGENTS.md)_
