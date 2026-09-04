<#
  Creates secrets.enc next to this script, encrypted with the fixed
  passphrase in Secrets-Passphrase.ps1 (shared with Build-Env.ps1).
  install.bat runs this automatically when secrets.enc is missing, so
  there is no prompt - this is convenience encryption, not real secrecy,
  since the passphrase lives in the repo alongside the encrypted file.

  secrets.enc already exists and is committed - you should not need to
  run this again. If a secret value ever changes: fill in the values
  below, run this script once to regenerate secrets.enc, then blank the
  values out again below (do NOT commit this file with real values in
  it - only secrets.enc, which is encrypted, should hold them at rest).

      powershell -NoProfile -ExecutionPolicy Bypass -File New-Secrets.ps1
#>

$ErrorActionPreference = 'Stop'
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
. (Join-Path $here 'Secrets-Passphrase.ps1')

# ====== FILL IN ONLY WHEN REGENERATING secrets.enc, THEN BLANK AGAIN ======
$secrets = [ordered]@{
    APP_KEY           = ''
    MAIL_USERNAME     = ''
    MAIL_PASSWORD     = ''
    PUSHER_APP_ID     = ''
    PUSHER_APP_KEY    = ''
    PUSHER_APP_SECRET = ''
}
# ===========================================================================

if ($secrets.Values | Where-Object { [string]::IsNullOrEmpty($_) }) {
    throw "secrets.enc already exists with real values encrypted inside it. Fill in the values in this script only if you actually need to regenerate secrets.enc, then blank them out again afterward."
}

function Get-KeyFromPassword {
    param([string]$PlainPassword, [byte[]]$Salt)
    $derive = New-Object System.Security.Cryptography.Rfc2898DeriveBytes(
        $PlainPassword, $Salt, 100000, [System.Security.Cryptography.HashAlgorithmName]::SHA256
    )
    return $derive.GetBytes(32)
}

$json = $secrets | ConvertTo-Json
$plainBytes = [Text.Encoding]::UTF8.GetBytes($json)

$salt = New-Object byte[] 16
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($salt)
$key = Get-KeyFromPassword -PlainPassword $SecretsPassphrase -Salt $salt

$aes = [Security.Cryptography.Aes]::Create()
$aes.Key = $key
$aes.GenerateIV()
$iv = $aes.IV

$encryptor = $aes.CreateEncryptor()
$cipherBytes = $encryptor.TransformFinalBlock($plainBytes, 0, $plainBytes.Length)
$aes.Dispose()

$outPath = Join-Path $here 'secrets.enc'
$fs = [IO.File]::Open($outPath, [IO.FileMode]::Create)
$fs.Write($salt, 0, $salt.Length)
$fs.Write($iv, 0, $iv.Length)
$fs.Write($cipherBytes, 0, $cipherBytes.Length)
$fs.Close()

Write-Host ""
Write-Host "secrets.enc written to $outPath" -ForegroundColor Green
