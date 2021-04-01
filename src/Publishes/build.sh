#!/bin/bash
echo 'optimize'
php artisan optimize
echo 'build admin frontend resources'
cd resources/admin && npm run build
echo 'pack dist files'
cd ../.. && php artisan build
echo 'optimize clear'
php artisan optimize:clear