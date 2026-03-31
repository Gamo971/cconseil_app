@echo off
chcp 65001 >nul

echo =============================================================
echo   CONSULTING OS - Arret
echo =============================================================
echo.

docker compose stop >nul 2>&1
if %errorlevel% neq 0 (
    docker-compose stop
)

echo  [OK] Tous les services sont arretes.
echo  Vos donnees sont conservees.
echo.
pause
