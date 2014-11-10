rm -Rf src/GravitonDyn/
php app/console -n graviton:generate:dynamicbundles --json resources/definition/Showcase.json
rm -Rf app/cache app/logs app/bootstrap.php.cache
./composer.phar install
php app/console server:run
