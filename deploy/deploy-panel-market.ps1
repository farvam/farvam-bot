# panel-market (Farvam marketing site): upload from Windows and install on the server (container panel-market).
# Usage: put this file next to panel-market-deploy.zip and run deploy-panel-market.bat (or this script).
param(
  [string]$Server = "193.24.120.197",
  [string]$User   = "root"
)
$ErrorActionPreference = "Stop"
$zip = Join-Path $PSScriptRoot "panel-market-deploy.zip"
if (-not (Test-Path $zip)) { Write-Host "panel-market-deploy.zip was not found next to this script." -ForegroundColor Red; exit 1 }
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) { Write-Host "OpenSSH client is missing (Settings > Apps > Optional features > OpenSSH Client)." -ForegroundColor Red; exit 1 }

Write-Host "`n[1/2] Uploading package to $User@$Server ... (enter the server password if asked)" -ForegroundColor Yellow
scp "$zip" "${User}@${Server}:/tmp/panel-market-deploy.zip"
if ($LASTEXITCODE -ne 0) { Write-Host "Upload failed." -ForegroundColor Red; exit 1 }

Write-Host "`n[2/2] Installing on the server ... (enter the password again if asked)" -ForegroundColor Yellow
$remote = "set -e; mkdir -p /opt/panel-market; rm -rf /opt/panel-market/site /opt/panel-market/docker; " +
          "python3 -m zipfile -e /tmp/panel-market-deploy.zip /opt/panel-market; rm -f /tmp/panel-market-deploy.zip; " +
          "bash /opt/panel-market/server-install.sh"
ssh -t "${User}@${Server}" $remote
if ($LASTEXITCODE -ne 0) { Write-Host "`nInstallation reported an error. Copy the output above and send it to Claude." -ForegroundColor Red; exit 1 }
Write-Host "`nFinished. Open https://farvamcertification.ir/farvam/" -ForegroundColor Green
