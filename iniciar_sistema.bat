@echo off
title Iniciador del Sistema PV
echo ==============================================
echo   Iniciando Sistema de Punto de Venta (PV)
echo ==============================================

echo [1/3] Iniciando Servidor Apache y MySQL (XAMPP)...
:: Navegamos a la carpeta de XAMPP para usar su ejecutable de inicio
cd /D C:\xampp
start "" /MIN xampp_start.exe

:: Esperamos 2 segundos para dar tiempo a que los servicios arranquen
timeout /t 2 /nobreak > nul

echo [2/3] Abriendo el menu en el navegador...
:: Abrimos la direccion localhost en el navegador por defecto
start http://localhost/PV/menu.html

echo [3/3] Iniciando el tunel ngrok...
:: Volvemos a tu carpeta y ejecutamos ngrok
cd /D C:\xampp\htdocs\PV
ngrok http 80
