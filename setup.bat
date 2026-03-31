@echo off
chcp 65001 >nul
cls

echo =============================================================
echo   CONSULTING OS - Installation initiale
echo =============================================================
echo.

REM === ETAPE 1/7 : Verification Docker ===================================
echo [1/7] Verification de Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  [ERREUR] Docker n est pas installe ou pas dans le PATH.
    echo.
    echo  Telechargez Docker Desktop ici :
    echo  https://www.docker.com/products/docker-desktop/
    echo.
    echo  Installez-le, redemarrez votre PC, puis relancez ce script.
    pause
    exit /b 1
)
echo  [OK] Docker detecte.

docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo  [ERREUR] Docker Desktop n est pas demarre.
    echo.
    echo  Solutions :
    echo   1. Ouvrez Docker Desktop depuis le menu Demarrer
    echo   2. Attendez que l icone Docker soit stable dans la barre des taches
    echo   3. Si besoin : Settings > General > "Use the WSL 2 based engine"
    echo   4. Relancez ce script
    echo.
    pause
    exit /b 1
)
echo  [OK] Docker est actif.
echo.

REM === ETAPE 2/7 : Creation du projet Laravel ============================
echo [2/7] Creation du projet Laravel dans src/...
if exist "src\artisan" (
    echo  [OK] Projet Laravel deja present, etape ignoree.
) else (
    docker run --rm -v "%cd%/src:/app" composer:latest composer create-project laravel/laravel:^11 /app --no-interaction --prefer-dist
    if %errorlevel% neq 0 (
        echo  [ERREUR] La creation du projet Laravel a echoue.
        pause
        exit /b 1
    )
    echo  [OK] Projet Laravel cree.
)
echo.

REM === ETAPE 3/7 : Copie des fichiers Consulting OS ======================
echo [3/7] Installation des fichiers Consulting OS (stubs)...
if exist "stubs" (
    robocopy stubs src /E /NFL /NDL /NJH /NJS >nul 2>&1
    echo  [OK] Fichiers application copies.
) else (
    echo  [ATTENTION] Dossier stubs introuvable - etape ignoree.
)
echo.

REM === ETAPE 4/7 : Configuration .env ====================================
echo [4/7] Configuration de l environnement (.env)...
powershell -ExecutionPolicy Bypass -File "configure-env.ps1"
echo.

REM === ETAPE 5/7 : Demarrage Docker ======================================
echo [5/7] Demarrage des services (app + nginx + mysql + phpmyadmin)...

REM Essai avec "docker compose" (V2, recommande)
docker compose up -d --build >nul 2>&1
if %errorlevel% neq 0 (
    REM Fallback sur "docker-compose" (V1)
    docker-compose up -d --build
    if %errorlevel% neq 0 (
        echo  [ERREUR] Impossible de demarrer les services Docker.
        echo  Verifiez que Docker Desktop est bien demarre.
        pause
        exit /b 1
    )
)
echo  [OK] Services Docker demarres.
echo.

REM === ETAPE 6/7 : Attente MySQL =========================================
echo [6/7] Attente de MySQL (25 secondes)...
timeout /t 25 /nobreak >nul
echo  [OK] Base de donnees prete.
echo.

REM === ETAPE 7/7 : Installation des dependances et migrations ============
echo [7/7] Configuration finale (dependances + migrations)...

docker exec consulting_os_app composer require laravel/breeze --dev --no-interaction
if %errorlevel% neq 0 (
    echo  [ERREUR] composer require laravel/breeze a echoue.
    echo  Le conteneur n est peut-etre pas encore pret. Attendez 10 secondes...
    timeout /t 10 /nobreak >nul
    docker exec consulting_os_app composer require laravel/breeze --dev --no-interaction
)

docker exec consulting_os_app php artisan breeze:install blade --no-interaction
docker exec consulting_os_app composer require anthropic-php/client guzzlehttp/guzzle --no-interaction
docker exec consulting_os_app npm install
docker exec consulting_os_app npm run build
docker exec consulting_os_app php artisan key:generate
docker exec consulting_os_app php artisan migrate --force
docker exec consulting_os_app php artisan migrate --path=database/migrations/consulting_os --force
docker exec consulting_os_app php artisan storage:link
docker exec consulting_os_app php artisan optimize:clear

echo  [OK] Configuration terminee.
echo.

echo =============================================================
echo   INSTALLATION REUSSIE !
echo =============================================================
echo.
echo   Application  ->  http://localhost:8080
echo   phpMyAdmin   ->  http://localhost:8082
echo.
echo   Creez votre compte sur http://localhost:8080/register
echo.
echo   IMPORTANT : Ajoutez votre cle API Claude dans src/.env :
echo   ANTHROPIC_API_KEY=sk-ant-...
echo.
echo =============================================================
pause
