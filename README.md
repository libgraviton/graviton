# Graviton 

[![Build Status](https://travis-ci.com/libgraviton/graviton.svg?branch=develop)](https://travis-ci.com/libgraviton/graviton) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Code Coverage](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Latest Stable Version](https://poser.pugx.org/graviton/graviton/v/stable.svg)](https://packagist.org/packages/graviton/graviton) [![Total Downloads](https://poser.pugx.org/graviton/graviton/downloads.svg)](https://packagist.org/packages/graviton/graviton) [![License](https://poser.pugx.org/graviton/graviton/license.svg)](https://packagist.org/packages/graviton/graviton)

Graviton is a Symfony and Doctrine Mongo ODM based REST server generation toolkit. So it stores all data in MongoDB.

You can define your REST service in an simple JSON format, run the generator - and voilà - your REST API is ready to go.

Let's say you define this file:

```json
{
  "id": "Example",
  "service": {
    "readOnly": false,
    "routerBase": "/example/endpoint/"
  },
  "target": {
    "fields": [
      {
        "name": "id",
        "type": "string"
      },
      {
        "name": "data",
        "type": "string"
      }
    ]
  }
}

``` 

Then you run the generator

```bash
php bin/console graviton:generate:dynamicbundles
```

And once running, you will have a full RESTful endpoint at `/example/endpoint`, supporting GET, POST, PUT, DELETE and PATCH as well as a valid
generated JSON schema endpoint, pagination headers (`Link` as github does it) and much more.

The generated code are static PHP files and configuration for the Serializer and Symfony and is regarded as _disposable_. You can always
regenerate it - don't touch the generated code.

The application is highly optimized for runtime performance, particurarly in the context of PHP-FPM with activated opcache.

It boasts many additional features (such as special validators and many flags and configurations) which are currently mostly undocumented as this project was not built for public usage in mind. But if
there is interest and support from outside users, we welcome questions and contributions.

## Install

```bash
composer install
```

## Usage

```bash
./dev-cleanstart.sh
```

and

```bash
php bin/console
```

## Documentation

There are some general docs on interacting with the codebase as a whole. 

- [Development](app/Resources/doc/DEVELOPMENT.md)
- [Deploy](app/Resources/doc/DEPLOY.md)

Some even broader scoped docs in a seperate repo.

- [docs.graviton.scbs.ch](https://docs.graviton.scbs.ch/)

The bundle readme files which show how to interact with
the various subsystems.

- [DocumentBundle](src/Graviton/DocumentBundle/README.md)
- [FileBundle](src/Graviton/FileBundle/README.md)
- [GeneratorBundle](src/Graviton/GeneratorBundle/README.md)
- [I18nBundle](src/Graviton/I18nBundle/README.md)
- [SecurityBundle](src/Graviton/SecurityBundle/README.md)
- [TestBundle](src/Graviton/TestBundle/README.md)
- [AnalyticsBundle](src/Graviton/AnalyticsBundle/README.md)

And not to forget, the all important [CHANGELOG](https://github.com/libgraviton/graviton/releases).
