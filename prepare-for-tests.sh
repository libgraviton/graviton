rm -Rf src/GravitonDyn var/cache/*
composer configure
SYMFONY_ENV=test composer install --ignore-platform-reqs --no-interaction
