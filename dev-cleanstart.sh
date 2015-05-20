export SYMFONY__GENERATOR__DYNAMICBUNDLES__MONGOCOLLECTION="LoadConfig"
export SYMFONY__GENERATOR__DYNAMICBUNDLES__MONGOCOLLECTION__CRITERIA='{"loadPackage":"szkb-wcp"}'

rm -Rf app/cache app/logs app/bootstrap.php.cache
composer install
php app/console doctrine:mongodb:fixtures:load
#php app/console server:run
