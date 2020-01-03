rm -Rf var/cache/* var/log/*
composer install
php bin/console doctrine:mongodb:schema:update
symfony serve --no-tls
