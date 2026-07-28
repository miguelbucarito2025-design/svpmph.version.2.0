@echo off
echo Generando documentacion con phpDocumentor...
C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe phpDocumentor.phar -d . -t docs
echo ¡Listo! Tu manual se ha actualizado en la carpeta /docs.
pause