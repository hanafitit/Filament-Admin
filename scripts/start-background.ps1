$WorkingDirectory = $env:SERVICE_WORKING_DIR
$CommandLine = $env:SERVICE_COMMAND_LINE
$PidFile = $env:SERVICE_PID_FILE
$StdOutFile = $env:SERVICE_STDOUT_FILE
$StdErrFile = $env:SERVICE_STDERR_FILE

if ([string]::IsNullOrWhiteSpace($WorkingDirectory) -or
    [string]::IsNullOrWhiteSpace($CommandLine) -or
    [string]::IsNullOrWhiteSpace($PidFile) -or
    [string]::IsNullOrWhiteSpace($StdOutFile) -or
    [string]::IsNullOrWhiteSpace($StdErrFile)) {
    throw "Missing SERVICE_* environment variables for background process startup."
}

$parentDir = Split-Path -Parent $PidFile
if (-not (Test-Path -LiteralPath $parentDir)) {
    New-Item -ItemType Directory -Path $parentDir -Force | Out-Null
}

$outDir = Split-Path -Parent $StdOutFile
if (-not (Test-Path -LiteralPath $outDir)) {
    New-Item -ItemType Directory -Path $outDir -Force | Out-Null
}

$errDir = Split-Path -Parent $StdErrFile
if (-not (Test-Path -LiteralPath $errDir)) {
    New-Item -ItemType Directory -Path $errDir -Force | Out-Null
}

$process = Start-Process `
    -FilePath "cmd.exe" `
    -ArgumentList @("/c", $CommandLine) `
    -WorkingDirectory $WorkingDirectory `
    -WindowStyle Hidden `
    -RedirectStandardOutput $StdOutFile `
    -RedirectStandardError $StdErrFile `
    -PassThru

Set-Content -LiteralPath $PidFile -Value $process.Id
