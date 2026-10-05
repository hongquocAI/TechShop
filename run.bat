@echo off
rem Chay TechShop: MySQL (Laragon, cong 3307) + web server cong 8000
set PATH=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;%PATH%
start "" /B C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe --defaults-file=C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini --port=3307 --mysqlx=OFF --socket=
timeout /t 8 >nul
php artisan serve --port=8000
