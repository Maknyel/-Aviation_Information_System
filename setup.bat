@echo off
echo Starting Backend...

cd backend
start cmd /k php artisan serve

echo Starting Frontend...

cd ../frontend
start cmd /k npm run dev

echo Both servers are starting...
pause