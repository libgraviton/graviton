# GravitonI18nBundle

Translate RESTful services as described in [the docs](https://gravity-platform-docs.nova.scapp.io/api/i18n/)

## Features

* translates all Documents that implement ``TranslatableDocumentInterface``
* creates missing translation strings on the fly
* has a command ``graviton:i118n:load:missing`` for generating missing i18n translatables after adding new languages
* exposes configuration of i18n over REST
  - ``/i18n/language`` allows managing languages the system speaks
  - ``/i18n/translatable`` allow users to manage translatable strings in all supported languages

## Inner Working

Most of the work is done during serialization and deserialization.

The ``I18nSerializationListener`` removes untranslated strings from documents and replaces
then with versions in all requested languages.

The ``I18nDeserializationListener`` makes sure that only english strings get added to
records of type ``TranslatableDocumentInterface`` in mongodb. All other languages
then get added as ``Translatable`` for later translation.

There is also a Doctrine/ODM listener that ensures that the translations get reloaded
after changing in mongodb.

As long as no translatables are changed the server should always use symfonys default
translation cache for i18n.

All translatables are stored in the ``Translatable`` collection in MongoDB. Their domain
corresponds to the bundle the document is from.
