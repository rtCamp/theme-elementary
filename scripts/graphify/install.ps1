<#
.SYNOPSIS
    Install the graphify CLI for developers (Windows / PowerShell).

.DESCRIPTION
    graphify ships on PyPI as the package "graphifyy" (double y) but installs a
    console script and Python module both named "graphify". The name mismatch is
    expected; it is not a typo.

    Installation method is chosen automatically: uv -> pipx -> pip.

.PARAMETER Method
    Force a method: uv, pipx, or pip. Defaults to auto.

.PARAMETER Gemini
    Also install the optional [gemini] extra.

.PARAMETER Upgrade
    Upgrade an existing install to the latest version.

.EXAMPLE
    ./install.ps1
    ./install.ps1 -Gemini
    ./install.ps1 -Method uv -Upgrade
#>
param(
    [ValidateSet('auto', 'uv', 'pipx', 'pip')]
    [string]$Method = 'auto',
    [switch]$Gemini,
    [switch]$Upgrade
)

$ErrorActionPreference = 'Stop'

$Package = 'graphifyy'
$Module  = 'graphify'
$Extra   = if ($Gemini) { '[gemini]' } else { '' }
$Spec    = "$Package$Extra"

function Write-Log  ($m) { Write-Host "==> $m" -ForegroundColor Cyan }
function Write-Warn2($m) { Write-Host "warn: $m" -ForegroundColor Yellow }
function Have ($cmd) { [bool](Get-Command $cmd -ErrorAction SilentlyContinue) }

# Already installed?
if (-not $Upgrade -and (Have $Module)) {
    Write-Log "graphify already on PATH: $((Get-Command $Module).Source)"
    Write-Log "Re-run with -Upgrade to update it."
    exit 0
}

# Resolve method
$chosen = $Method
if ($chosen -eq 'auto') {
    if     (Have 'uv')     { $chosen = 'uv' }
    elseif (Have 'pipx')   { $chosen = 'pipx' }
    elseif (Have 'python') { $chosen = 'pip' }
    elseif (Have 'py')     { $chosen = 'pip' }
    else {
        Write-Warn2 "No installer found. Install one of: uv, pipx, or Python + pip."
        Write-Warn2 "  uv:   https://docs.astral.sh/uv/getting-started/installation/"
        Write-Warn2 "  pipx: https://pipx.pypa.io/stable/installation/"
        exit 1
    }
}
Write-Log "Using method: $chosen"

$py = if (Have 'python') { 'python' } else { 'py' }

switch ($chosen) {
    'uv' {
        if ($Upgrade) {
            Write-Log "Upgrading $Spec via uv..."
            uv tool upgrade $Package 2>$null
            if ($LASTEXITCODE -ne 0) { uv tool install --reinstall $Spec }
        } else {
            Write-Log "Installing $Spec via uv..."
            uv tool install $Spec
        }
    }
    'pipx' {
        if ($Upgrade) {
            Write-Log "Upgrading $Spec via pipx..."
            pipx upgrade $Package 2>$null
            if ($LASTEXITCODE -ne 0) { pipx install --force $Spec }
        } else {
            Write-Log "Installing $Spec via pipx..."
            pipx install $Spec
        }
    }
    'pip' {
        Write-Log "Installing $Spec via $py -m pip..."
        & $py -m pip install --upgrade $Spec
        if ($LASTEXITCODE -ne 0) {
            Write-Warn2 "Plain install failed; retrying with --user..."
            & $py -m pip install --upgrade --user $Spec
        }
    }
}

# Verify
if (Have $Module) {
    Write-Log "Installed. 'graphify' is on PATH at: $((Get-Command $Module).Source)"
    try { & $Module --help | Out-Null } catch { }
    Write-Host " ok: graphify is ready." -ForegroundColor Green
} else {
    Write-Warn2 "Install finished but 'graphify' is not on PATH yet."
    Write-Warn2 "Open a new terminal, or ensure your tool bin dir is on PATH:"
    Write-Warn2 "  uv:   run 'uv tool update-shell'"
    Write-Warn2 "  pipx: run 'pipx ensurepath'"
    exit 1
}
