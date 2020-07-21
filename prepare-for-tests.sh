rm -Rf src/GravitonDyn var/cache/*
SYMFONY_ENV=test composer install --ignore-platform-reqs --no-interaction
