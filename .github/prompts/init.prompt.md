---
mode: agent
description: Initialize or manage this theme using its declared dependencies and npm run init; confirm identity, example selection, and optional features before changes.
---

# /init

Follow the theme-specific [init skill](../../.claude/skills/init/SKILL.md) as the canonical workflow, and read [AGENTS.md](../../AGENTS.md) for guardrails. This prompt and the Claude skill share the same installation, identity, capability, consent, and scaffold-handoff behavior.

Gather missing setup or manage inputs in one message, show derived values and the resolved command, and wait for consent before installation or rewriting. Do not assume a theme name. Use Copilot's task surface to track the workflow where available.

- Fresh setup: `composer install` invokes npm installation. If Composer runs with `--no-scripts`, run `npm install` separately.
- Defaults: retain examples, HMR on, Tailwind and Dev Tools off. Dev Tools is an optional private dependency with its own access requirement.
- `--list` (optionally `--json`) and help are read-only and do not run `sync-ai`.
- `--yes` needs `--name` only in setup mode. Manage-mode feature toggles need no name.
- Decline the wizard's Git initialization; never perform commits or remote writes.
- Cleanup removes this prompt directory. Explain that limitation before running init; the retained Claude skill and raw CLI remain available.
- For a setup feature brief, remove demo sets during init and hand each requested feature to the [/scaffold prompt](scaffold.prompt.md) if retained, or the [scaffold skill](../../.claude/skills/scaffold/SKILL.md). Theme classes register directly in `Main::CLASSES`.

Do not duplicate the init engine or rewrite generated `.wp-scaffold.json` manually. Report changes and outstanding developer actions, and refresh the local graph as specified by AGENTS.md.
