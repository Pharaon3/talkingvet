@echo off
cd "C:\apache\htdocs"
cmd /c "php artisan app:clean-login-tokens"