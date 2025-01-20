@echo off
:loop
"C:\xampp\php\php.exe" -f "C:\xampp\htdocs\deadstock\admin\auto-bid.php"
timeout /t 1
goto loop