# configure-env.ps1
# Configure le fichier src\.env pour Docker (MySQL, APP_NAME)
# Appele par setup.bat - ne pas lancer directement

param(
    [string]$EnvPath = "src\.env"
)

if (-not (Test-Path $EnvPath)) {
    Copy-Item "src\.env.example" $EnvPath
    Write-Host "  [OK] Fichier .env cree depuis .env.example"
} else {
    Write-Host "  [OK] Fichier .env deja present"
    exit 0
}

$content = Get-Content $EnvPath -Raw

# Nom de l'application
$content = $content -replace 'APP_NAME=Laravel', 'APP_NAME="Consulting OS"'

# Base de donnees MySQL (Docker)
$content = $content -replace 'DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql'
$content = $content -replace '# DB_HOST=127\.0\.0\.1', 'DB_HOST=db'
$content = $content -replace '# DB_PORT=3306', 'DB_PORT=3306'
$content = $content -replace '# DB_DATABASE=laravel', 'DB_DATABASE=consulting_os'
$content = $content -replace '# DB_USERNAME=root', 'DB_USERNAME=laravel'
$content = $content -replace '# DB_PASSWORD=', 'DB_PASSWORD=laravel_password'

# Si les parametres DB sont deja sans commentaire (Laravel 11 par defaut)
$content = $content -replace 'DB_HOST=127\.0\.0\.1', 'DB_HOST=db'
$content = $content -replace 'DB_DATABASE=laravel', 'DB_DATABASE=consulting_os'
$content = $content -replace 'DB_USERNAME=root', 'DB_USERNAME=laravel'
$content = $content -replace '^DB_PASSWORD=$', 'DB_PASSWORD=laravel_password'

Set-Content $EnvPath $content -Encoding UTF8
Write-Host "  [OK] Configuration .env appliquee (MySQL Docker)"
