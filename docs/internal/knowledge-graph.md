# Knowledge graph

This theme ships a committed knowledge graph so AI assistants — and you — can
answer structural questions by querying a graph instead of reading through
`inc/`, `src/`, and the framework's own source by hand. It's built with the
[Graphify](https://graphify.net) utility. The committed graph may lag behind
your checkout: use current files when checking exact signatures or behavior.

## Install (one time)

```bash
scripts/graphify/install.sh   # auto-detects uv / pipx / pip
graphify install               # registers the /graphify skill for your assistant
```

See the [bundled setup instructions](../../scripts/graphify/README.md) for the
Windows/PowerShell route and troubleshooting. The graph is a navigation aid;
you can develop and build the theme without it.

## Use the graph

Once installed, run from the theme root:

```bash
graphify query "how does Main load its classes"
graphify path "Main" "Logger"
graphify explain "Util"
graphify affected "Assets"
```

Or run the `/graphify` skill in Claude Code and ask in natural language.

## Refresh locally

After renaming the theme or changing its structure:

```bash
graphify update .
graphify cluster-only . --no-label --no-viz
```

`update` re-extracts the code and rebuilds the structural graph; `cluster-only`
refreshes `graphify-out/GRAPH_REPORT.md` without regenerating the HTML
visualization. The maintained repository artifacts are `graphify-out/graph.json`
and `graphify-out/GRAPH_REPORT.md`. Regenerating committed artifacts is a
separate maintainer decision; see [maintenance](maintenance.md#knowledge-graph).

## Optional: semantic layer

The committed graph is structural only. An optional semantic layer (community
naming, inferred "why" edges) needs an LLM, via either an API key (for example
`GEMINI_API_KEY`) with `graphify extract . --mode deep`, or by running the
`/graphify` skill inside Claude Code, which uses the active session instead of
a key.
