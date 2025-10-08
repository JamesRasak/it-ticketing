@echo off
REM Quick importer for the ticketing schema in XAMPP
set DB_NAME=ticketing
set DB_USER=root
set DB_PASS=
set MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe
"%MYSQL_BIN%" -u %DB_USER% -p%DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"%MYSQL_BIN%" -u %DB_USER% -p%DB_PASS% %DB_NAME% < ..\..\schema\schema.sql
if %errorlevel% neq 0 (
  echo Import failed. Check credentials or MYSQL_BIN path.
) else (
  echo Schema imported successfully into %DB_NAME%.
)
