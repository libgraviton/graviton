rm -Rf app/cache app/logs app/bootstrap.php.cache
composer install
php app/console doctrine:mongodb:fixtures:load
php app/console doctrine:mongodb:schema:update
php app/console server:run
