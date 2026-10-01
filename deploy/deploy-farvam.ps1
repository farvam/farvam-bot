# Farvam site: upload from Windows and install on the server (container farvam-site).
# Usage: put this file next to farvam-deploy.zip and run deploy-farvam.bat (or this script).
param(
  [string]$Server = "193.24.120.197",
  [string]$User   = "root"
)
$ErrorActionPreference = "Stop"
$zip = Join-Path $PSScriptRoot "farvam-deploy.zip"
if (-not (Test-Path $zip)) { Write-Host "farvam-deploy.zip was not found next to this script." -ForegroundColor Red; exit 1 }
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) { Write-Host "OpenSSH client is missing (Settings > Apps > Optional features > OpenSSH Client)." -ForegroundColor Red; exit 1 }

Write-Host "`n[1/2] Uploading package to $User@$Server ... (enter the server password if asked)" -ForegroundColor Yellow
scp "$zip" "${User}@${Server}:/tmp/farvam-deploy.zip"
if ($LASTEXITCODE -ne 0) { Write-Host "Upload failed." -ForegroundColor Red; exit 1 }

Write-Host "`n[2/2] Installing on the server ... (enter the password again if asked)" -ForegroundColor Yellow
$remote = "set -e; mkdir -p /opt/farvam-site; rm -rf /opt/farvam-site/site /opt/farvam-site/docker; " +
          "python3 -m zipfile -e /tmp/farvam-deploy.zip /opt/farvam-site; rm -f /tmp/farvam-deploy.zip; " +
          "bash /opt/farvam-site/server-install.sh"
ssh -t "${User}@${Server}" $remote
if ($LASTEXITCODE -ne 0) { Write-Host "`nInstallation reported an error. Copy the output above and send it to Claude." -ForegroundColor Red; exit 1 }
Write-Host "`nFinished. Open https://farvamcertification.ir/farvam/" -ForegroundColor Green
