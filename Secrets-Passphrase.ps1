<#
  Fixed passphrase used to encrypt/decrypt secrets.enc, shared by
  New-Secrets.ps1 and Build-Env.ps1 so install.bat can run end-to-end
  with no prompts. This is convenience encryption (keeps secrets.enc
  out of plain text at rest), not real secrecy - anyone with this repo
  can read this file too.
#>

$SecretsPassphrase = 'Aviation-Info-Sys-Local-Install-2026'
