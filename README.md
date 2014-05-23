# Graviton

[![Build Status](https://travis-ci.org/libgraviton/graviton.png?branch=master)](https://travis-ci.org/libgraviton/graviton)

Graviton is a symfony2 based REST server.

## Developing

Clone the Repo and install composer dependencies to start.

````bash
git clone https://github.com/libgraviton/graviton.git
cd graviton
composer install
````

You can now start a development server.

````bash
php app/console server:run
````

Please run ``./vendor/bin/phpunit -c app/`` and ``./vendor/bin/php-cs-fixer fix src/`` before commiting changes.
