@echo off
echo === Docker-based Database Setup ===
echo.
echo This script sets up the database using Docker containers.
echo.

echo Starting Docker containers...
docker compose up -d
echo.

echo Waiting for containers to be ready...
timeout /t 10 /nobreak > nul
echo.

echo Setting up database inside Docker container...
docker compose exec project-management-php php setup_database.php
echo.

echo Setup complete! 
echo Visit: http://localhost:8080/public/login.php
echo Login with: john.smith / p@ssW0rd1234
echo.
pause
