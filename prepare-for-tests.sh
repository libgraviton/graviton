rm -Rf src/GravitonDyn
export GENERATOR_SYNTHETIC_FIELDS='int:clientId'
export GENERATOR_SYNTHETIC_FIELDS_EXPOSE_ON="/testcase/rest-listeners-cond-persister"
export DATA_RESTRICTION_MAP="{x-graviton-client: 'int:clientId'}"
composer configure
composer install --ignore-platform-reqs --no-interaction
