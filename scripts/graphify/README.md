# graphify install scripts

Helper scripts to install the **graphify** CLI that the `/graphify` Claude Code
skill depends on. Run these yourself: the skill cannot install the package on
your behalf, because the package name (`graphifyy`) differs from the command it
provides (`graphify`), which the agent safety classifier flags as a possible
typosquat and blocks.

> **The name mismatch is expected.** The PyPI distribution is `graphifyy`
> (double y); the installed console script and Python import are both
> `graphify`. This is how the tool is published, not a typo.

## Quick start

### macOS / Linux

```bash
scripts/graphify/install.sh            # install (auto-detects uv / pipx / pip)
scripts/graphify/install.sh --gemini   # also install the optional Gemini extra
scripts/graphify/install.sh --upgrade  # upgrade an existing install
scripts/graphify/verify.sh             # confirm it is importable + on PATH
```

If the scripts are not executable yet:

```bash
chmod +x scripts/graphify/*.sh
```

### Windows (PowerShell)

```powershell
scripts/graphify/install.ps1
scripts/graphify/install.ps1 -Gemini
scripts/graphify/install.ps1 -Upgrade
```

You may need to allow local scripts for the session first:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
```

## What the installer does

1. Picks an installation method automatically, preferring isolation:
   1. **uv** (`uv tool install`) - recommended, fastest, fully isolated
   2. **pipx** (`pipx install`) - isolated per-tool virtualenv
   3. **pip** (`python3 -m pip install`) - falls back to `--user`, then to
      `--break-system-packages --user` on PEP 668 externally-managed environments
2. Installs (or upgrades) the `graphifyy` distribution.
3. Verifies that `graphify` is importable by an interpreter the `/graphify`
   skill can discover, using the same detection order the skill uses.

Force a specific method with `--method uv|pipx|pip` (shell) or
`-Method uv|pipx|pip` (PowerShell).

## After installing

If the `graphify` command is not found in a fresh shell, your tool bin
directory is probably not on `PATH`:

- **uv:** `uv tool update-shell`, then restart the shell
- **pipx:** `pipx ensurepath`, then restart the shell

Then re-run `/graphify .` in Claude Code: it will detect the installed
interpreter and proceed with the build.

## Files

| File            | Purpose                                              |
| --------------- | ---------------------------------------------------- |
| `install.sh`    | macOS / Linux installer (uv -> pipx -> pip fallback) |
| `install.ps1`   | Windows PowerShell installer                         |
| `verify.sh`     | Confirm graphify is installed and importable         |
| `uninstall.sh`  | Remove graphify (tries each installer's uninstall)   |

## Manual install (no scripts)

Any one of these works:

```bash
uv tool install graphifyy        # recommended
pipx install graphifyy
python3 -m pip install graphifyy
```
