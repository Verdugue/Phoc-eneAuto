@echo off
title Phoceenne Auto - Server Launcher
color 0A

:: Définir le chemin vers PHP de WAMP64
set PHP_PATH=C:\wamp64\bin\php\php8.2.18\php.exe
set PORT=9090

:: Vérifier si PHP existe dans WAMP64
if not exist "%PHP_PATH%" (
    color 0C
    echo PHP n'a pas été trouvé dans WAMP64
    echo Veuillez vérifier le chemin : %PHP_PATH%
    echo.
    pause
    exit /b
)

:: Tuer les processus PHP existants sur le port spécifié
for /f "tokens=5" %%a in ('netstat -aon ^| find ":%PORT%"') do (
    taskkill /F /PID %%a >nul 2>&1
)

:: Attendre un moment pour que le port soit libéré
timeout /t 2 >nul

cls
echo ================================
echo    Phoceenne Auto - Serveur
echo ================================
echo.
echo Serveur: http://localhost:%PORT%
echo.
echo Appuyez sur Ctrl+C pour arrêter le serveur
echo.

:: Démarrer le navigateur
start http://localhost:%PORT%

:: Démarrer le serveur PHP avec le chemin complet
php -S localhost:9090 router.php 