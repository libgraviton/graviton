# Changelog

## 0.7.0

This release concludes some months of development without release. It contains heaps
of backwards incompatible changes. In future we will be doing releases more often.

Trying to summarize this release contains much work in the following areas:

* dynamic-bundle support that we use to generate graviton-based apps
* a lot of infrastructure work
* countless fixes and changes
* much very important stuff

## 0.6.0

* full i18n support
  * translatable strings stored in ``/i18n/translatable``
  * supported languages manageable through ``/i18n/language``
  * ``/core/app`` is now i18n enabled
  * multilingual schemas (base on a v5 proposal to json+schema)
  * multilingual properties get marked as translatable to aid frontend developer
  * makes use of Accept-Language and Content-Language for language negotiations
  * See (the docs)[http://gravity-platform.github.io/doc/i18n.html] for more information
* fix loading of VCAP_* env vars in cloudfoundry
* support getting some headers in CORS responses
* update all the things: mostly symfony 2.5

## 0.5.0

* make graviton installable on cloudfoundry out of the box
* CORS support
* return schema in OPTIONS responses
  * as suggested in RFC2616
  * uses ``application/schema+json`` mime type
  * schema responses have a rel=canonical link for easier caching

## 0.4.0

* adds pagination support on resources
  * paging information is returned in Link headers
  * all resource collections are now returned paged with a hardlimit at 10 items per page
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
