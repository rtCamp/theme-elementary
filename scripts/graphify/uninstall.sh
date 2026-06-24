#!/usr/bin/env bash
#
# uninstall.sh - remove the graphify CLI (macOS / Linux).
#
# Tries each installer's uninstall path. Harmless to run even if graphify was
# installed by a different method; unmatched methods are skipped quietly.
#
# Usage:
#   ./uninstall.sh
#
set -uo pipefail

PACKAGE="graphifyy"
MODULE="graphify"

log()  { printf '\033[1;34m==>\033[0m %s\n' "$*"; }
have() { command -v "$1" >/dev/null 2>&1; }

removed="0"

if have uv; then
  log "Attempting: uv tool uninstall $PACKAGE"
  if uv tool uninstall "$PACKAGE" 2>/dev/null; then removed="1"; fi
fi

if have pipx; then
  log "Attempting: pipx uninstall $PACKAGE"
  if pipx uninstall "$PACKAGE" 2>/dev/null; then removed="1"; fi
fi

for cand in python3 python; do
  if have "$cand"; then
    log "Attempting: $cand -m pip uninstall -y $PACKAGE"
    if "$cand" -m pip uninstall -y "$PACKAGE" 2>/dev/null; then removed="1"; fi
  fi
done

if [ "$removed" = "1" ]; then
  log "Uninstall steps completed."
else
  log "Nothing was uninstalled (graphify may not have been installed by these methods)."
fi

if have "$MODULE"; then
  printf '\033[1;33mnote:\033[0m the "%s" command is still on PATH at %s; it may be installed another way.\n' \
    "$MODULE" "$(command -v "$MODULE")" >&2
fi
