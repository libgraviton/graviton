# GravitonCoreBundle

## Inner Working

### version
While bootstrapping the version numbers are fetched from versions.yml and saved into the container using a compiler pass.

The version numbers are accessible trough the container as `graviton.core.version.data`.

For example: `$container->getParameter('graviton.core.version.data');`
