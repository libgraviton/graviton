# Graviton

[![Build Status](https://travis-ci.org/libgraviton/graviton.png?branch=master)](https://travis-ci.org/libgraviton/graviton) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=master) [![Latest Stable Version](https://poser.pugx.org/graviton/graviton/v/stable.svg)](https://packagist.org/packages/graviton/graviton) [![Total Downloads](https://poser.pugx.org/graviton/graviton/downloads.svg)](https://packagist.org/packages/graviton/graviton) [![Latest Unstable Version](https://poser.pugx.org/graviton/graviton/v/unstable.svg)](https://packagist.org/packages/graviton/graviton) [![License](https://poser.pugx.org/graviton/graviton/license.svg)](https://packagist.org/packages/graviton/graviton)

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
