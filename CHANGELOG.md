# Changelog

## 0.4.0

* adds pagination support on resources
** paging information is returned in Link headers
** all resource collections are now returned paged with a hardlimit at 10 items per page
* cleans up type hinting throughout the code base
* refactors writing of link headers
* replaces factory code with symfony dic code in some instances
* incorporates a lot of feedback from scrutinizer-ci

## 0.3.0

* basic REST support
  * create doctrine_odm based REST services
  * supports read-only services and writable service
* new endpoints
  * ``/core/app``
  * ``/taxonomy/country``
* acceptance tests with phpunit
* travis-ci and scrutinizer-ci integration
