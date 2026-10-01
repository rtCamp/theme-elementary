#!/usr/bin/env bash
#
# install.sh - install the graphify CLI for developers (macOS / Linux).
#
# graphify ships on PyPI as the package "graphifyy" (double y) but installs a
# console script and Python module both named "graphify". The name mismatch is
# expected; it is not a typo on your part.
#
# Installation method is chosen automatically in this order of preference:
#   1. uv   (uv tool install)        - isolated, fastest, recommended
#   2. pipx (pipx install)           - isolated per-tool venv
#   3. pip  (python3 -m pip install) - falls back to --user, then PEP 668 override
#
# Usage:
#   ./install.sh                 # install (or upgrade) graphify
#   ./install.sh --gemini        # also install the optional Gemini extra
#   ./install.sh --method uv     # force a specific method: uv | pipx | pip
#   ./install.sh --upgrade       # upgrade an existing install to the latest
#   ./install.sh --help
#
set -euo pipefail

PACKAGE="graphifyy"          # PyPI distribution name
MODULE="graphify"            # import name + console script name
EXTRA=""                      # set to "[gemini]" when --gemini is passed
METHOD="auto"
UPGRADE="0"

# ---- argument parsing -------------------------------------------------------
while [ "$#" -gt 0 ]; do
  case "$1" in
    --gemini)  EXTRA="[gemini]"; shift ;;
    --upgrade) UPGRADE="1"; shift ;;
    --method)  METHOD="${2:-auto}"; shift 2 ;;
    --method=*) METHOD="${1#*=}"; shift ;;
    -h|--help)
      # Print the contiguous header comment block (lines after the shebang
      # up to the first non-comment line), stripping the leading "# ".
      awk 'NR==1{next} /^#/{sub(/^# ?/,""); print; next} {exit}' "$0"
      exit 0 ;;
    *)
      echo "Unknown argument: $1" >&2
      echo "Run '$0 --help' for usage." >&2
      exit 2 ;;
  esac
done

SPEC="${PACKAGE}${EXTRA}"

log()  { printf '\033[1;34m==>\033[0m %s\n' "$*"; }
warn() { printf '\033[1;33mwarn:\033[0m %s\n' "$*" >&2; }
err()  { printf '\033[1;31merror:\033[0m %s\n' "$*" >&2; }

have() { command -v "$1" >/dev/null 2>&1; }

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# ---- already installed? -----------------------------------------------------
if [ "$UPGRADE" = "0" ] && have "$MODULE"; then
  log "graphify already on PATH at: $(command -v "$MODULE")"
  log "Re-run with --upgrade to update it. Verifying import..."
  if [ -x "$SCRIPT_DIR/verify.sh" ] && "$SCRIPT_DIR/verify.sh"; then exit 0; fi
fi

# ---- method resolution ------------------------------------------------------
resolve_method() {
  case "$METHOD" in
    uv|pipx|pip) echo "$METHOD"; return ;;
    auto)
      if have uv;   then echo uv;   return; fi
      if have pipx; then echo pipx; return; fi
      if have python3 || have python; then echo pip; return; fi
      err "No installer found. Install one of: uv, pipx, or python3 + pip."
      err "  uv:   https://docs.astral.sh/uv/getting-started/installation/"
      err "  pipx: https://pipx.pypa.io/stable/installation/"
      exit 1 ;;
    *)
      err "Unknown --method '$METHOD' (expected: uv | pipx | pip)"; exit 2 ;;
  esac
}

PY="python3"; have python3 || PY="python"

install_uv() {
  if [ "$UPGRADE" = "1" ]; then
    log "Upgrading $SPEC via uv..."
    uv tool upgrade "$PACKAGE" 2>/dev/null || uv tool install --reinstall "$SPEC"
  else
    log "Installing $SPEC via uv..."
    uv tool install "$SPEC"
  fi
}

install_pipx() {
  if [ "$UPGRADE" = "1" ]; then
    log "Upgrading $SPEC via pipx..."
    pipx upgrade "$PACKAGE" 2>/dev/null || pipx install --force "$SPEC"
  else
    log "Installing $SPEC via pipx..."
    pipx install "$SPEC"
  fi
}

install_pip() {
  local flags="--upgrade"
  log "Installing $SPEC via $PY -m pip..."
  if "$PY" -m pip install $flags "$SPEC" 2>/dev/null; then return 0; fi
  warn "Plain pip install failed; retrying with --user..."
  if "$PY" -m pip install $flags --user "$SPEC" 2>/dev/null; then return 0; fi
  warn "Environment looks externally managed (PEP 668)."
  warn "Retrying with --break-system-packages --user (safe for a user-level tool)..."
  "$PY" -m pip install $flags --user --break-system-packages "$SPEC"
}

CHOSEN="$(resolve_method)"
log "Using method: $CHOSEN"

case "$CHOSEN" in
  uv)   install_uv ;;
  pipx) install_pipx ;;
  pip)  install_pip ;;
esac

# ---- verify -----------------------------------------------------------------
if [ -x "$SCRIPT_DIR/verify.sh" ]; then
  "$SCRIPT_DIR/verify.sh" || {
    warn "Install finished but verification could not import graphify."
    warn "If the 'graphify' command is not found, ensure your tool bin dir is on PATH:"
    warn "  uv:   ~/.local/bin    (run: uv tool update-shell)"
    warn "  pipx: ~/.local/bin    (run: pipx ensurepath)"
    exit 1
  }
else
  log "Done. Verify with: $MODULE --help"
fi
