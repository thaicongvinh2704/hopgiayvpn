$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$releasePath = Join-Path $PSScriptRoot 'runtime\release-wp'
New-Item -ItemType Directory -Force -Path $releasePath,(Join-Path $releasePath 'wp-content\plugins'),(Join-Path $releasePath 'wp-content\mu-plugins') | Out-Null
Get-ChildItem (Join-Path $PSScriptRoot 'runtime\wp') -File -Filter '*.php' | Where-Object { $_.Name -ne 'wp-config.php' } | Copy-Item -Destination $releasePath
foreach ($dir in @('wp-includes','wp-admin')) {
    if (!(Test-Path (Join-Path $releasePath $dir))) {
        if ($dir -eq 'wp-admin') { Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'runtime\wp\wp-admin') -Destination (Join-Path $releasePath $dir) -Recurse }
        else { New-Item -ItemType Junction -Path (Join-Path $releasePath $dir) -Target (Join-Path $repo $dir) | Out-Null }
    }
}
if (!(Test-Path (Join-Path $releasePath 'wp-content\themes'))) { New-Item -ItemType Junction -Path (Join-Path $releasePath 'wp-content\themes') -Target (Join-Path $repo 'wp-content\themes') | Out-Null }
$testConfig = [IO.File]::ReadAllText((Join-Path $PSScriptRoot 'test-config.php'))
[IO.File]::WriteAllText((Join-Path $releasePath 'wp-config.php'),$testConfig.Replace("`$table_prefix='vct_';","`$table_prefix='rel_';").Replace('127.0.0.1:8091','127.0.0.1:8092'),[Text.UTF8Encoding]::new($false))
Copy-Item (Join-Path $PSScriptRoot 'test-mu.php') (Join-Path $releasePath 'wp-content\mu-plugins\test-sink.php')
Expand-Archive -LiteralPath (Join-Path $repo 'artifacts\vpn-live-chat\vpn-live-chat-1.0.0.zip') -DestinationPath (Join-Path $releasePath 'wp-content\plugins') -Force
& C:\xampp\php\php.exe (Join-Path $PSScriptRoot 'release.php')
if ($LASTEXITCODE -ne 0) { throw 'Release lifecycle test failed' }
