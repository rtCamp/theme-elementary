#!/usr/bin/env bash
#
# verify.sh - confirm the graphify CLI is installed and importable.
#
# Exit codes:
#   0  graphify is importable (and, ideally, on PATH)
#   1  graphify is not importable by any discoverable interpreter
#
# This mirrors the interpreter-detection the /graphify skill uses, so a
# successful run here means the skill will find graphify too.
#
set -uo pipefail

MODULE="graphify"

log()  { printf '\033[1;34m==>\033[0m %s\n' "$*"; }
ok()   { printf '\033[1;32m ok:\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33mwarn:\033[0m %s\n' "$*" >&2; }

have() { command -v "$1" >/dev/null 2>&1; }

# 1. Is the console script on PATH?
if have "$MODULE"; then
  ok "command found: $(command -v "$MODULE")"
  "$MODULE" --version 2>/dev/null || "$MODULE" --help >/dev/null 2>&1 || true
fi

# 2. Find an interpreter that can import it (uv tool, pipx, venv, system).
PYTHON=""

# 2a. uv-managed tool environment.
if [ -z "$PYTHON" ] && have uv; then
  _UV_PY="$(uv tool run graphifyy python -c 'import sys; print(sys.executable)' 2>/dev/null || true)"
  if [ -n "$_UV_PY" ]; then PYTHON="$_UV_PY"; fi
fi

# 2b. Shebang of the installed console script (pipx / direct pip).
if [ -z "$PYTHON" ] && have "$MODULE"; then
  _BIN="$(command -v "$MODULE")"
  _SHEBANG="$(head -1 "$_BIN" | tr -d '#!' || true)"
  case "$_SHEBANG" in
    *[!a-zA-Z0-9/_.-]*) ;;  # ignore odd shebangs (e.g. /usr/bin/env python)
    *) if [ -n "$_SHEBANG" ] && "$_SHEBANG" -c "import $MODULE" 2>/dev/null; then PYTHON="$_SHEBANG"; fi ;;
  esac
fi

# 2c. Fall back to system interpreters.
if [ -z "$PYTHON" ]; then
  for cand in python3 python; do
    if have "$cand" && "$cand" -c "import $MODULE" 2>/dev/null; then PYTHON="$cand"; break; fi
  done
fi

if [ -z "$PYTHON" ]; then
  warn "Could not import '$MODULE' with any discoverable interpreter."
  warn "If you just installed it, your tool bin dir may not be on PATH yet:"
  warn "  uv:   run 'uv tool update-shell' then restart your shell"
  warn "  pipx: run 'pipx ensurepath' then restart your shell"
  exit 1
fi

VERSION="$("$PYTHON" -c "import $MODULE, importlib.metadata as m; print(m.version('graphifyy'))" 2>/dev/null || echo 'unknown')"
ok "import works via: $PYTHON"
ok "graphifyy version: $VERSION"
log "graphify is ready. The /graphify skill will detect this interpreter."
exit 0
