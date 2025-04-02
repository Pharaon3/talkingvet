@echo off
cd "C:\apache24\htdocs\laravel_dictation_portal"
cmd /c "php artisan queue:work -v"