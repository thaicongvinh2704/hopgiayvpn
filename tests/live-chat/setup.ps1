$ErrorActionPreference = 'Stop'
$repo = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$runtime = Join-Path $PSScriptRoot 'runtime\wp'
New-Item -ItemType Directory -Force -Path $runtime,(Join-Path $runtime 'wp-content'),(Join-Path $runtime 'wp-content\mu-plugins') | Out-Null
foreach ($dir in @('wp-includes')) {
    $link = Join-Path $runtime $dir
    if (!(Test-Path $link)) { New-Item -ItemType Junction -Path $link -Target (Join-Path $repo $dir) | Out-Null }
}
$adminPath = Join-Path $runtime 'wp-admin'
if ((Test-Path $adminPath) -and ((Get-Item -LiteralPath $adminPath).Attributes -band [IO.FileAttributes]::ReparsePoint)) {
    # Delete just the junction itself; never traverse the source checkout.
    [IO.Directory]::Delete($adminPath)
}
if (!(Test-Path $adminPath)) { Copy-Item -LiteralPath (Join-Path $repo 'wp-admin') -Destination $adminPath -Recurse }
foreach ($dir in @('plugins','themes')) {
    $link = Join-Path $runtime "wp-content\$dir"
    if (!(Test-Path $link)) { New-Item -ItemType Junction -Path $link -Target (Join-Path $repo "wp-content\$dir") | Out-Null }
}
Get-ChildItem -LiteralPath $repo -Filter '*.php' | Where-Object { $_.Name -notlike 'wp-config*' } | Copy-Item -Destination $runtime
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'test-config.php') -Destination (Join-Path $runtime 'wp-config.php')
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'test-mu.php') -Destination (Join-Path $runtime 'wp-content\mu-plugins\test-sink.php')
& C:\xampp\php\php.exe (Join-Path $PSScriptRoot 'install.php')
if ($LASTEXITCODE -ne 0) { throw 'Test WordPress setup failed' }
