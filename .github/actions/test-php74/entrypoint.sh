#!/bin/bash

bash prepare-for-tests.sh

export MONGODB_URI=mongodb://mongodb:27017/db
composer configure

composer check

php -m

php vendor/bin/phpunit --coverage-clover=coverage.clover
