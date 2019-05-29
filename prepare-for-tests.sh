rm -Rf src/GravitonDyn
export GENERATOR_SYNTHETIC_FIELDS='int:clientId'
export DATA_RESTRICTION_MAP="{x-graviton-client: 'int:clientId'}"
rm -f app/config/parameters.yml
composer run-script post-install-cmd
