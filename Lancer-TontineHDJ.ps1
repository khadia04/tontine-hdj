# ==========================================
# Lancement automatique de WampServer
# et de l'application Tontine HDJ
# ==========================================

$WampPath = "C:\wamp64\wampmanager.exe"
$AppUrl = "http://localhost/tontine-hdj"

# Vérification de Wamp
if (!(Test-Path $WampPath)) {
    [System.Windows.Forms.MessageBox]::Show(
        "WampServer est introuvable : $WampPath",
        "Erreur",
        0,
        16
    )
    exit
}

# Démarrer Wamp
Start-Process $WampPath

# Attendre qu'Apache soit prêt
$Pret = $false
$Tentatives = 0

while (-not $Pret -and $Tentatives -lt 30) {
    try {
        Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing -TimeoutSec 2 | Out-Null
        $Pret = $true
    }
    catch {
        Start-Sleep -Seconds 2
        $Tentatives++
    }
}

# Ouvrir l'application
if ($Pret) {
    Start-Process $AppUrl
}
else {
    [System.Windows.Forms.MessageBox]::Show(
        "Impossible de démarrer le serveur Web.",
        "Erreur",
        0,
        16
    )
}