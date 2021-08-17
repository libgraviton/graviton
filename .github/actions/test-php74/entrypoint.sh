#!/bin/bash

bash prepare-for-tests.sh

export MONGODB_URI=mongodb://mongodb:27017/db
composer configure

composer check

php vendor/bin/phpunit
