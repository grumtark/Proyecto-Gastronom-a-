@echo off
title Lanzador de Mi App PHP

:: 1. VERIFICACION servidor ejecutandose
tasklist /fi "imagename eq php.exe" /fi "windowtitle eq PHP*" | find "php.exe" >nul
if not errorlevel 1 (
    echo El servidor ya está ejecutándose.
    goto abrir_navegador
)

:: 2. INICIA EL SERVIDOR en segundo plano
echo Iniciando servidor PHP...
cd /d "C:\ProyecGastro"  ← Ruta al proyecto
start "Servidor PHP" php -S localhost:8000
echo Servidor iniciado en segundo plano.

:: 3. ABRE EL NAVEGADOR automaticamente
:abrir_navegador
timeout /t 1 /nobreak >nul
echo Abriendo navegador...
start http://localhost:8000/

:: 4. CIERRA automaticamente después de 3 segundos
echo ¡Listo! Servidor: http://localhost:8000
echo Esta ventana se cerrará en 3 segundos...
timeout /t 3 /nobreak >nul
exit