@echo off
chcp 65001 >nul
cls

echo =============================================================
echo   CONSULTING OS - Demarrage
echo =============================================================
echo.

docker compose up -d >nul 2>&1
if %errorlevel% neq 0 (
    docker-compose up -d
    if %errorlevel% neq 0 (
        echo  [ERREUR] Impossible de demarrer. Docker est-il lance ?
        pause
        exit /b 1
    )
)

echo  [OK] Services demarres !
echo.
echo   Application  ->  http://localhost:8080
echo   phpMyAdmin   ->  http://localhost:8082
echo.

start http://localhost:8080

echo  Appuyez sur une touche pour fermer...
pause >nul
