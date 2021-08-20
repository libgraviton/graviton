#!/bin/bash

bash prepare-for-tests.sh

export MONGODB_URI=mongodb://mongodb:27017/db
composer configure

composer check

GITHUB_TOKEN=$1

php -dextension=pcov -dpcov.enabled=1 -dpcov.directory=${PWD}/src/Graviton -dpcov.exclude="~vendor~" vendor/bin/phpunit --coverage-clover ${PWD}/coverage.clover --log-junit ${PWD}/phpunit_junit.xml

composer global require php-coveralls/php-coveralls
COVERALLS_REPO_TOKEN=${GITHUB_TOKEN} php ${HOME}/.composer/vendor/bin/php-coveralls --coverage_clover=${PWD}/coverage.clover --root_dir=${PWD}/src/Graviton/ -o /tmp/coveralls.json -v
