# ==========================================
# Lancement automatique de XAMPP
# et de l'application Tontine HDJ
# ==========================================

Add-Type -AssemblyName System.Windows.Forms

$XamppControl = "C:\xampp\xampp-control.exe"
$ApacheExe    = "C:\xampp\apache_start.bat"
$MySqlExe     = "C:\xampp\mysql_start.bat"

$AppUrl = "http://localhost/tontine-hdj"

# Vérification de XAMPP
if (!(Test-Path $XamppControl)) {
    [System.Windows.Forms.MessageBox]::Show(
        "XAMPP est introuvable : $XamppControl",
        "Erreur",
        0,
        16
    )
    exit
}

# Lancer le panneau XAMPP
Start-Process $XamppControl

Start-Sleep -Seconds 3

# Démarrer Apache
if (Test-Path $ApacheExe) {
    Start-Process $ApacheExe -WindowStyle Hidden
}

# Démarrer MySQL
if (Test-Path $MySqlExe) {
    Start-Process $MySqlExe -WindowStyle Hidden
}

# Attendre que localhost réponde
$Pret = $false
$Tentatives = 0

while (-not $Pret -and $Tentatives -lt 30) {

    try {

        Invoke-WebRequest `
            -Uri "http://localhost" `
            -UseBasicParsing `
            -TimeoutSec 2 | Out-Null

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
        "Impossible de démarrer Apache.",
        "Erreur",
        0,
        16
    )

}