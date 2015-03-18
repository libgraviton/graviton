rm -Rf app/cache app/logs app/bootstrap.php.cache
./composer.phar install
php app/console doctrine:mongodb:fixtures:load
php app/console server:run
