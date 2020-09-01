bash prepare-for-tests.sh
php bin/console doctrine:mongodb:schema:update
symfony serve --no-tls
