@echo off
REM Increase PHP memory for this session
SET PHP_MEMORY_LIMIT=4096M

REM Directories to scan
SET DIRS=includes tests vendor-prefixed

REM Loop through each directory and fix PHPCS issues
FOR %%D IN (%DIRS%) DO (
    echo ==============================================
    echo Running PHPCBF on directory: %%D
    php -d memory_limit=%PHP_MEMORY_LIMIT% vendor\bin\phpcbf -p --standard=WordPress %%D
)

REM Fix the root files
echo ==============================================
echo Running PHPCBF on root files
php -d memory_limit=%PHP_MEMORY_LIMIT% vendor\bin\phpcbf -p --standard=WordPress *.php

echo ==============================================
echo PHPCS auto-fix complete!
pause
