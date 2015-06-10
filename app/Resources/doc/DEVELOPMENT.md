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

## Custom Environments

Some bundles (as `SecurityBundle`) support custom environments (please refer to their documentation for specifics).

To support other environments then the standard ones, Graviton provides an `ENV` aware router you can use like this:

```bash
SYMFONY_ENV=<customEnv> php app/console --router=src/Graviton/CoreBundle/Resources/config/router_env.php server:run
```

Graviton also respects the `SYMFONY_ENV` environment variable when invocated by `app.php` (i.e. in a webserver context).

## Code Generators

There are various code generators available at your disposal.

Peruse their documentation in [GeneratorBundle](../../../src/Graviton/GeneratorBundle/README.md).
