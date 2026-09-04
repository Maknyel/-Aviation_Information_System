<#
  Called by install.bat. Decrypts secrets.enc (creating it first via
  New-Secrets.ps1 if it doesn't exist yet), fills in
  backend\.env.template, and writes backend\.env. Fully non-interactive.
#>

param(
    [Parameter(Mandatory)] [string]$DbName
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
. (Join-Path $root 'Secrets-Passphrase.ps1')

$secretsPath  = Join-Path $root 'secrets.enc'
$templatePath = Join-Path $root '.env.template'
$outputPath   = Join-Path $root 'backend\.env'

if (-not (Test-Path $secretsPath)) {
    & (Join-Path $root 'New-Secrets.ps1')
}
if (-not (Test-Path $templatePath)) { throw ".env.template not found at $templatePath" }

function Unprotect-Secrets {
    param([string]$Path, [string]$PlainPassword)

    $bytes = [IO.File]::ReadAllBytes($Path)
    $salt        = $bytes[0..15]
    $iv          = $bytes[16..31]
    $cipherBytes = $bytes[32..($bytes.Length - 1)]

    $derive = New-Object Security.Cryptography.Rfc2898DeriveBytes(
        $PlainPassword, [byte[]]$salt, 100000, [Security.Cryptography.HashAlgorithmName]::SHA256
    )
    $key = $derive.GetBytes(32)

    $aes = [Security.Cryptography.Aes]::Create()
    $aes.Key = $key
    $aes.IV  = $iv

    $decryptor = $aes.CreateDecryptor()
    try {
        $plainBytes = $decryptor.TransformFinalBlock($cipherBytes, 0, $cipherBytes.Length)
    } catch {
        throw "Decryption failed - wrong passphrase or a corrupted secrets.enc."
    } finally {
        $aes.Dispose()
    }

    return [Text.Encoding]::UTF8.GetString($plainBytes)
}

$json = Unprotect-Secrets -Path $secretsPath -PlainPassword $SecretsPassphrase
$secrets = $json | ConvertFrom-Json

$template = Get-Content -Raw $templatePath

foreach ($prop in $secrets.PSObject.Properties) {
    $template = $template.Replace("{{$($prop.Name)}}", [string]$prop.Value)
}
$template = $template.Replace('{{DB_NAME}}', $DbName)

if ($template -match '\{\{[A-Z0-9_]+\}\}') {
    throw "Missing value for placeholder $($Matches[0]) - check secrets.enc contents."
}

Set-Content -Path $outputPath -Value $template -Encoding UTF8
Write-Host "backend\.env written successfully." -ForegroundColor Green
