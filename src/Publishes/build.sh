#!/bin/bash
echo 'cache config'
php artisan config:cache
echo 'build admin frontend resources'
cd resources/admin && npm run build
echo 'pack dist files'
cd ../.. && php artisan build
echo 'cache clear'
php artisan config:cache
