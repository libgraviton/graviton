# GravitonCoreBundle

## Inner Working

### version
While bootstrapping the version numbers are fetched from versions.yml and saved into the container using a compiler pass.
For this to work, composer and git have to be available on the build-enviroment during build-time.

The version numbers are accessible trough the container as `graviton.core.version.data`.

For example: `$container->getParameter('graviton.core.version.data');`

#### How to configure which version are reported

In the folder `app/config/` you can find a file called `version_service.yml` where you can add/remove packages.

##### An example for `version_service.yml`

```
desiredVersions:

  - self
  
  - graviton/graviton
```

#### How to (re)generate versions.yml
* Make sure that composer and git are available on your machine either in $PATH or at a location that can be defined in the app/config/parameters.yml:
```
...
    graviton.composer.cmd: composer
    graviton.git.cmd: /usr/bin/git
...
```
This is just an example. The default values are plain "composer" and "git", assuming that these commands are in $PATH

* run
```
php app/console graviton:core:generateversions
```

### adding additional endpoints to the main page

When creating a new service which is not generated, you run into the problem that your hardcoded endpoint won't show
on the main page.

To fix this problem you have to register it in ```config.yml```. There you'll find the key graviton_core 
where you can add the service name and the uri.

#### an example: 


```yml
graviton_core:
      service_name:
        - graviton.core.static.version.get
        - graviton.security.static.whoami.get
      uri_whitelist:
        - /core/version
        - /person/whoami
```