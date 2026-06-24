# Knowledge graph (Graphify)

This repo ships a committed knowledge graph so AI assistants can answer questions by
querying a graph instead of reading the whole corpus (benchmarked at 20x-250x fewer
tokens per question). It is built with the official
[Graphify](https://graphify.net) utility (`graphifyy` on PyPI; the CLI is `graphify`).

## What is tracked

A graph of this theme repo (`theme-elementary`): its `inc/` classes, the `Main::CLASSES`
wiring, assets, and how they relate. The graph can optionally be extended to cover the
framework it builds on by checking the dependencies out as siblings under the same parent
directory and merging (see Regenerate):

- `theme-elementary` (this repo)
- `wp-framework` (the framework in `vendor/`, mirrored as a sibling for source-level nodes)
- `wp-tooling` (the init/scaffold engine)

Committed artifacts (only these two; everything else in `graphify-out/` is gitignored):

- `graphify-out/graph.json` - the queryable graph (nodes, edges, communities, per-node `repo` tag).
- `graphify-out/GRAPH_REPORT.md` - human-readable summary (god nodes, communities).

## Install (one time)

Use the bundled helper, which auto-detects `uv` / `pipx` / `pip`:

```bash
./scripts/graphify/install.sh    # installs the graphifyy CLI; ./scripts/graphify/verify.sh checks it
graphify install                 # registers the /graphify skill
```

This environment uses an externally managed Python; if you install by hand, prefer pipx:
`pipx install graphifyy` (the CLI stays `graphify`).

## Query (this is what saves tokens)

```bash
graphify query "how does Main load its classes"
graphify path "Main" "Logger"
graphify explain "Util"
graphify affected "Assets"
```

Or run the `/graphify` skill in Claude Code and ask in natural language.

## Regenerate

The structural rebuild is fully local (tree-sitter, no API key). After changes:

```bash
# This repo only (the committed default):
graphify update .
graphify cluster-only . --no-label --no-viz   # refresh GRAPH_REPORT.md, skip HTML

# Optional cross-repo graph (run from this repo's root, siblings checked out under ..):
ROOT="$(cd .. && pwd)"
graphify update .
( cd "$ROOT/wp-framework" && graphify update . )
( cd "$ROOT/wp-tooling"   && graphify update . )
graphify merge-graphs \
  graphify-out/graph.json \
  "$ROOT/wp-framework/graphify-out/graph.json" \
  "$ROOT/wp-tooling/graphify-out/graph.json" \
  --out graphify-out/graph.json
graphify cluster-only . --no-label --no-viz
rm -rf "$ROOT"/wp-framework/graphify-out "$ROOT"/wp-tooling/graphify-out
```

## Optional: semantic layer

The committed graph is structural only. The optional semantic layer (community naming,
inferred "why" edges) needs an LLM, via either an API key (for example `GEMINI_API_KEY`)
with `graphify extract . --mode deep`, or by running the `/graphify` skill inside Claude
Code, which uses the active session instead of a key.
