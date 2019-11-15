rm -Rf src/GravitonDyn
export GENERATOR_SYNTHETIC_FIELDS='int:clientId'
export DATA_RESTRICTION_MAP="{x-graviton-client: 'int:clientId'}"
composer configure
composer install --ignore-platform-reqs --no-interaction
