rm -Rf src/GravitonDyn/
php app/console -n graviton:generate:dynamicbundles --json
rm -Rf app/cache app/logs app/bootstrap.php.cache
./composer.phar install
php app/console doctrine:mongodb:fixtures:load
php app/console server:run
