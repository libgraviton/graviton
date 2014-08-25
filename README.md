# Graviton

[![Build Status](https://travis-ci.org/libgraviton/graviton.png?branch=develop)](https://travis-ci.org/libgraviton/graviton) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Code Coverage](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Latest Stable Version](https://poser.pugx.org/graviton/graviton/v/stable.svg)](https://packagist.org/packages/graviton/graviton) [![Total Downloads](https://poser.pugx.org/graviton/graviton/downloads.svg)](https://packagist.org/packages/graviton/graviton) [![Latest Unstable Version](https://poser.pugx.org/graviton/graviton/v/unstable.svg)](https://packagist.org/packages/graviton/graviton) [![License](https://poser.pugx.org/graviton/graviton/license.svg)](https://packagist.org/packages/graviton/graviton)

Graviton is a symfony2 based REST server.

## Features

While this project is still in its infancy it already implements some REST resources.

* ``/core/app`` exposes app objects and supports rw operation
* ``/core/product`` exposes a ro collection of products that are expected to be loaded for an erp
* ``/entity/country`` exposes a list of countries from The World Bank, it only supports ro operation
* ``/person/consultant`` exposes a list of customer sales reps and only supports ro operation
* ``/i18n/language`` and ``/i18n/translatable`` expose rw collections that may be used for various i18n purposes

The implemented resources are mainly for validating the server and we will be adding
more services as soon as that has been done.

The data available in the services are loaded with DataFixtures to ease development but will also
be populated by external means when we get nearer to production.

The graviton server returns a list of all the available resource collections in response to ``GET /``.

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

Please run ``./vendor/bin/phpunit`` and ``./vendor/bin/php-cs-fixer fix src/`` before commiting changes.

### Code Generators

There are various code generators available at you disposal.

If you are prompted for a config type you should always choose xml
since xml is the only format we are currently supporting. If you
use other formats, chances are you will have to fix and/or implement
features that where only added to the xml templates.

#### Generate new Bundle

````bash
php app/console graviton:generate:bundle --namespace=Acme/FooBundle --dir=src --bundle-name=AcmeFooBundle
````

#### Generate a new Resource

````bash
php app/console graviton:generate:resource --entity=AcmeFooBundle:Bar --format=xml --fields="name:string" --with-repository
php app/console graviton:generate:resource --entity=AcmeFooBundle:Baz --format=xml --fields="name:string isTrue:boolean consultant:Graviton\\PersonBundle\\Document\\Consultant" --with-repository
````

You will need to clean up the newly generated models Resource/schema/<name>.json file. 

## Quality Control

Graviton uses [travis-ci](http://travis-ci.org) and [scrutinizer-ci](http://scrutinizer-ci.com).

You will need to log in to scrutinizer once using you github account so you may be added to the project as admin or moderator as needed.

## Profiling graviton

If you expect performance issues you may try to find them by profiling the code.

````bash
php -d xdebug.profiler_enable=1 -d xdebug.profiler_output_dir=./ vendor/bin/phpunit -c app/
````

This generates a files called ``cachegrind.out.<PPID>`` that you may inspect using kcachegrind or a similar tool.

## Deploying

Graviton supports Cloudfoundry out of the box. You need to define a mongodb service called ``graviton-dev-mongo``. Pushing to the cloud
will push the bare code and run ``composer install`` on the target. Currently cloud installs get populated with mongodb fixtures on each
start.

``bash
cf push graviton-dev
``

### Loading fixtures in cloudfoundry.

Add the connection data from ``vcap_services.mongodb-2.2[0].credentials.url`` to ``app/config/parameters_local.xml`` but replace
the ``<ip>:<port>`` part with ``localhost:8001``.

``xml
<?xml version="1.0" encoding="UTF-8"?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <parameters>
    <parameter key="mongodb.default.server.uri">mongodb://user:pass@127.0.0.1:8001/db</parameter>
  </parameters>
</container>
``

Open a local connection using the service connector console.

``bash
sc connect 8001 -u $USERNAME -p $PASSWORD  graviton-dev-mongo
``

Load the fixtures using app/console.

``bash
php app/console doctrine:mongodb:fixtures:load
``
