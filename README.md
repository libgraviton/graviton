# Graviton

[![Build Status](https://travis-ci.org/libgraviton/graviton.png?branch=develop)](https://travis-ci.org/libgraviton/graviton) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Code Coverage](https://scrutinizer-ci.com/g/libgraviton/graviton/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/libgraviton/graviton/?branch=develop) [![Latest Stable Version](https://poser.pugx.org/graviton/graviton/v/stable.svg)](https://packagist.org/packages/graviton/graviton) [![Dependency Status](https://www.versioneye.com/user/projects/5798511374848d004b927939/badge.svg?style=flat)](https://www.versioneye.com/user/projects/5798511374848d004b927939) [![Total Downloads](https://poser.pugx.org/graviton/graviton/downloads.svg)](https://packagist.org/packages/graviton/graviton) [![License](https://poser.pugx.org/graviton/graviton/license.svg)](https://packagist.org/packages/graviton/graviton)

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
