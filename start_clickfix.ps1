# start_clickfix.ps1
# Start Laravel dev server and Cloudflared tunnel if not already running.
# Designed to be run at system startup (runs as SYSTEM when scheduled task is created).

$ErrorActionPreference = 'Continue'

$phpArgs = 'artisan serve --host=127.0.0.1 --port=8000'
$phpWd = 'C:\project\laravel_app_real'
$cloudflaredPath = 'C:\project\cloudflared.exe'
$cloudArgs = 'tunnel --url http://localhost:8000 --logfile C:\project\cloudflared.log'
$latestTunnelUrlPath = 'C:\project\tunnel_url.txt'

function ProcessRunningByCmdline($match) {
    try {
        $procs = Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -and $_.CommandLine -like $match }
        return ($procs -ne $null -and $procs.Count -gt 0)
    } catch {
        return $false
    }
}

function Get-TunnelUrlFromLog {
    param([string]$LogPath)

    if (-not (Test-Path $LogPath)) { return $null }

    $content = Get-Content -Path $LogPath -ErrorAction SilentlyContinue
    if (-not $content) { return $null }

    foreach ($line in $content) {
        if ($line -match 'https?://[A-Za-z0-9.-]+\.trycloudflare\.com') {
            return ($Matches[0])
        }
    }

    return $null
}

# Start PHP artisan serve if not found
if (-not (ProcessRunningByCmdline('*artisan*serve*'))) {
    try {
        Start-Process -FilePath 'php' -ArgumentList $phpArgs -WorkingDirectory $phpWd -WindowStyle Hidden -ErrorAction Stop | Out-Null
    } catch {
        Write-Output "Failed to start php artisan serve: $_"
    }
}

# Restart cloudflared to avoid stale expired quick-tunnel hosts
try {
    $cfProc = Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | Where-Object { $_.Name -eq 'cloudflared.exe' }
    if ($cfProc) {
        foreach ($p in $cfProc) {
            Stop-Process -Id $p.ProcessId -Force -ErrorAction SilentlyContinue
        }
    }
} catch {
    Write-Output "Unable to stop stale cloudflared processes: $_"
}

if (Test-Path $cloudflaredPath) {
    try {
        $null = Start-Process -FilePath $cloudflaredPath -ArgumentList $cloudArgs -WorkingDirectory 'C:\project' -WindowStyle Hidden -ErrorAction Stop
        Start-Sleep -Seconds 8
        $url = Get-TunnelUrlFromLog -LogPath 'C:\project\cloudflared.log'
        if ($url) {
            $url | Set-Content -Path $latestTunnelUrlPath
            Write-Output "Cloudflared URL: $url"
        } else {
            Write-Output 'Cloudflared started, but no tunnel URL has been logged yet.'
        }
    } catch {
        Write-Output "Failed to start cloudflared: $_"
    }
} else {
    Write-Output "cloudflared executable not found at $cloudflaredPath"
}

# End of script
