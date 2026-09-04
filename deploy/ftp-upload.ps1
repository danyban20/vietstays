param(
    [string[]]$LocalFiles = @(),
    [string]$LocalDir = '',
    [string]$RemoteDir = '',
    [string]$ConfigPath = ''
)

function Resolve-ConfigPath {
    param([string]$Provided)
    if ($Provided -and (Test-Path $Provided)) {
        return (Resolve-Path $Provided).Path
    }

    $fromScript = Join-Path (Split-Path $PSScriptRoot -Parent) 'sftp-config.json'
    if (Test-Path $fromScript) {
        return (Resolve-Path $fromScript).Path
    }

    $fromCwd = Join-Path (Get-Location) 'sftp-config.json'
    if (Test-Path $fromCwd) {
        return (Resolve-Path $fromCwd).Path
    }

    throw 'Missing sftp-config.json in project root.'
}

function Get-FtpConfig {
    param([string]$Path)
    if (-not (Test-Path $Path)) {
        throw "Missing FTP config: $Path"
    }

    $raw = [System.IO.File]::ReadAllText($Path)
    $config = [pscustomobject]@{
        FtpHost    = ''
        User       = ''
        Password   = ''
        RemoteBase = 'vietstays.com/public_html'
    }

    if ($raw -match '"host"\s*:\s*"([^"]+)"') {
        $config.FtpHost = $matches[1]
    }
    if ($raw -match '"user"\s*:\s*"([^"]+)"') {
        $config.User = $matches[1]
    }
    if ($raw -match '"password"\s*:\s*"([^"]+)"') {
        $config.Password = $matches[1]
    }
    if ($raw -match '"remote_path"\s*:\s*"([^"]+)"') {
        $config.RemoteBase = $matches[1].Trim('/')
    }

    if (-not $config.FtpHost -or -not $config.User -or -not $config.Password) {
        throw 'FTP config is missing host, user, or password.'
    }

    return $config
}

function New-FtpUri {
    param([string]$FtpHost, [string]$RemotePath)
    $normalized = ($RemotePath -replace '\\', '/').Trim('/')
    return "ftp://${FtpHost}/${normalized}"
}

function Ensure-FtpDirectory {
    param(
        [string]$FtpHost,
        [System.Net.NetworkCredential]$Credential,
        [string]$RemotePath
    )

    $parts = ($RemotePath -replace '\\', '/').Trim('/').Split('/')
    $current = @()
    foreach ($part in $parts) {
        if ([string]::IsNullOrWhiteSpace($part)) { continue }
        $current += $part
        $uri = New-FtpUri -FtpHost $FtpHost -RemotePath ($current -join '/')
        try {
            $request = [System.Net.FtpWebRequest]::Create($uri)
            $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $request.Credentials = $Credential
            $request.UsePassive = $true
            $request.UseBinary = $true
            $response = $request.GetResponse()
            $response.Close()
        } catch {
            # Directory likely already exists.
        }
    }
}

function Send-FtpFile {
    param(
        [string]$FtpHost,
        [System.Net.NetworkCredential]$Credential,
        [string]$LocalPath,
        [string]$RemotePath
    )

    $remoteDir = ($RemotePath -replace '\\', '/').Trim('/')
    if ($remoteDir.Contains('/')) {
        $parent = ($remoteDir -split '/')[0..($remoteDir.Split('/').Length - 2)] -join '/'
        Ensure-FtpDirectory -FtpHost $FtpHost -Credential $Credential -RemotePath $parent
    }

    $uri = New-FtpUri -FtpHost $FtpHost -RemotePath $remoteDir
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = $Credential
    $request.UsePassive = $true
    $request.UseBinary = $true
    $request.KeepAlive = $false
    $bytes = [System.IO.File]::ReadAllBytes($LocalPath)
    $request.ContentLength = $bytes.Length
    $stream = $request.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $response = $request.GetResponse()
    $response.Close()
    Write-Output "Uploaded $LocalPath -> ftp://${FtpHost}/${remoteDir}"
}

$config = Get-FtpConfig -Path (Resolve-ConfigPath $ConfigPath)
$credential = New-Object System.Net.NetworkCredential($config.User, $config.Password)
$remoteRoot = $config.RemoteBase.Trim('/')
$projectRoot = (Get-Location).Path
$uploaded = 0

foreach ($file in $LocalFiles) {
    if (-not (Test-Path $file)) {
        throw "Missing local file: $file"
    }
    $resolved = (Resolve-Path $file).Path
    $relative = $resolved.Substring($projectRoot.Length).TrimStart('\', '/')
    $remotePath = "$remoteRoot/$($relative -replace '\\', '/')"
    Send-FtpFile -FtpHost $config.FtpHost -Credential $credential -LocalPath $resolved -RemotePath $remotePath
    $uploaded++
}

if ($LocalDir -and $RemoteDir) {
    $resolvedLocal = (Resolve-Path $LocalDir).Path
    Get-ChildItem -Path $resolvedLocal -Recurse -File | ForEach-Object {
        $relative = $_.FullName.Substring($resolvedLocal.Length).TrimStart('\', '/')
        $remotePath = "$remoteRoot/$RemoteDir/$relative" -replace '\\', '/'
        Send-FtpFile -FtpHost $config.FtpHost -Credential $credential -LocalPath $_.FullName -RemotePath $remotePath
        $uploaded++
    }
}

Write-Output "Deploy upload complete. Files uploaded: $uploaded"
