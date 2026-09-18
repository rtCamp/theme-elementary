# Knowledge graph

The optional Graphify integration indexes this theme's classes, files, and
relationships for code navigation. The committed graph may lag behind your
checkout: use current files when checking exact signatures or behavior.

## Use the graph

If Graphify is installed, run from the theme root:

```bash
graphify query "how does Main load its classes"
graphify explain "Util"
graphify affected "Assets"
```

For installation, use the bundled [Graphify setup instructions](../../scripts/graphify/README.md).
The graph is a navigation aid; you can develop and build the theme without it.

## Refresh locally

After renaming the theme or changing its structure:

```bash
graphify update .
```

This refreshes the local structural graph. The maintained repository artifacts
are `graphify-out/graph.json` and `graphify-out/GRAPH_REPORT.md`. Regenerating
committed artifacts is a separate maintainer decision; see
[maintenance](maintenance.md#knowledge-graph).

## Optional: semantic layer

The committed graph is structural only. An optional semantic layer (community
naming, inferred "why" edges) needs an LLM, via either an API key (for example
`GEMINI_API_KEY`) with `graphify extract . --mode deep`, or by running the
`/graphify` skill inside Claude Code, which uses the active session instead of
a key.
