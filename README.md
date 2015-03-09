# Graviton

[![Build Status](https://travis-ci.org/libgraviton/graviton.png?branch=develop)](https://travis-ci.org/libgraviton/graviton) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Code Coverage](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Latest Stable Version](https://poser.pugx.org/graviton/graviton/v/stable.svg)](https://packagist.org/packages/graviton/graviton) [![Total Downloads](https://poser.pugx.org/graviton/graviton/downloads.svg)](https://packagist.org/packages/graviton/graviton) [![License](https://poser.pugx.org/graviton/graviton/license.svg)](https://packagist.org/packages/graviton/graviton)

Graviton is a symfony2 based REST server and server generation toolkit.

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
app/console
```

## Documentation

There are some general docs on interacting with the codebase as a whole. 

- [Development](app/Resources/doc/DEVELOPMENT.md)
- [Deploy](app/Resources/doc/DEPLOY.md)

Some even broader scoped docs in a seperate repo.

- [gravity-platform.github.io](http://gravity-platform.github.io/)

The bundle readme files which show how to interact with
the various subsystems.

- [GeneratorBundle](src/Graviton/GeneratorBundle/README.md)
- [I18nBundle](src/Graviton/I18nBundle/README.md)
- [SecurityBundle](src/Graviton/SecurityBundle/README.md)
- [TestBundle](src/Graviton/TestBundle/README.md)

And not to forget, the all important [CHANGELOG](CHANGELOG.md).
