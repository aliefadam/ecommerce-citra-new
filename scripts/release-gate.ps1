param(
    [switch]$SkipBrowser,
    [switch]$SkipInstall
)

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repoRoot

function Invoke-Gate([string]$Name, [scriptblock]$Command) {
    Write-Host "`n[release-gate] $Name"
    & $Command
    if ($LASTEXITCODE -ne 0) {
        throw "Release gate failed: $Name"
    }
}

if (-not $SkipInstall) {
    Invoke-Gate 'Composer lock install' { composer install --no-interaction --prefer-dist --no-progress }
    Invoke-Gate 'NPM lock install' { npm ci --ignore-scripts }
}

Invoke-Gate 'Composer manifest' { composer validate --no-check-publish }
Invoke-Gate 'Composer security audit' { composer audit --no-interaction }
Invoke-Gate 'NPM security audit' { npm audit --audit-level=high }
Invoke-Gate 'Backend regression' { php artisan test --compact }
Invoke-Gate 'Production asset build' { npm run build }

if (-not $SkipBrowser) {
    Invoke-Gate 'Browser critical E2E' { npm run test:e2e }
}

Invoke-Gate 'Patch whitespace' { git diff --check }

Write-Host "`n[release-gate] Automated candidate gates PASS. Production configuration and evidence gates follow."
php artisan ops:production-check
$productionExit = $LASTEXITCODE
php artisan ops:go-live-review
$reviewExit = $LASTEXITCODE

if ($productionExit -ne 0 -or $reviewExit -ne 0) {
    Write-Error 'NO-GO: automated tests passed, but production configuration/evidence is incomplete.'
    exit 1
}

Write-Host '[release-gate] GO UNTUK PILOT — requires owner sign-off recorded in release artifact.'
