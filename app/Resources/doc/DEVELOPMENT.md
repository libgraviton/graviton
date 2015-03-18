# Development

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

Please run ``./vendor/bin/phpunit`` and ``composer check`` before
commiting changes to ensure that travis will be ok with your changes.

## Code Generators

There are various code generators available at your disposal.

Peruse their documentation in [GeneratorBundle](../../../src/Graviton/GeneratorBundle/README.md).
