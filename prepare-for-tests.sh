rm -Rf src/GravitonDyn var/cache/*
APP_ENV=test composer install --ignore-platform-reqs --no-interaction
rm -Rf var/cache/*
