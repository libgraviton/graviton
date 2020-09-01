rm -Rf src/GravitonDyn var/cache/*
SYMFONY_ENV=test SYMFONY__GENERATOR__BUNDLEBUNDLE__ADDITIONS='["\\Graviton\\TestServicesBundle\\GravitonTestServicesBundle"]' composer install --ignore-platform-reqs --no-interaction
