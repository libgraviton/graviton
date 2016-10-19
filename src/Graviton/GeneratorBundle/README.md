# Graviton GeneratorBundle

If you are prompted for a config type you should always choose xml
since xml is the only format we are currently supporting. If you
use other formats, chances are you will have to fix and/or implement
features that where only added to the xml templates.

### Generate new Bundle

```bash
php app/console graviton:generate:bundle --namespace=Graviton/FooBundle --dir=src --bundle-name=GravitonFooBundle
```

The generated bundle assumes thaet you will be adding resources to it. If you do not plan on doing so
you will need to fix this by removing the doctrine and serializer config in ``Resources/config/config.xml``.

### Generate a new Resource

```bash
php app/console graviton:generate:resource --entity=GravitonFooBundle:Bar --format=xml --fields="name:string"
php app/console graviton:generate:resource --entity=GravitonFooBundle:Baz --format=xml --fields="name:string isTrue:boolean consultant:Graviton\\PersonBundle\\Document\\Consultant"
```
You will need to clean up the models Resource/schema/<name>.json file after generation. You may replace titles and you must
add descriptions in the fields marked @todo.

### Generate a Dynamic Bundle

```bash
php app/console graviton:generate:dynamicbundle --json
```
The workflow is as follows:

* Generate a BundleBundle, implementing the GravitonBundleInterface
* Generate our Bundles per JSON file
* Creating the necessary resources and files inside the newly created bundles.
* All that in our own GravitonDyn namespace.

Important: Why are we calling subcommand in their own process rather than just using
symfonys API? The main problem is, that we want to add resources (like
Documents) to our Bundles *directly* after generating them. Using the
internal API, we cannot add resources there using our tools as those Bundles
haven't been loaded yet through the AppKernel. Using ``shell_exec`` or similar
we can do that.

This shouldn't be a dealbreaker as this task is only used on deployment and/or
development where a shell is accessible. It should be executed in the same context
as the previous generator tools, and also those used the shell (backtick operator
to get git name/email for example).

#### Dynamic bundle caching mechanism

There is a mechanism that only regenerates existing dynamic bundles when necessary.

The following changes lead to a regeneration of the bundle:
* The JSON definition file of a service has changes (content)
* Any of the templates files in `Resources/skeleton` in this bundle change (keeping track per `mtime`)

**Please note:** If you're developing on the Generator itself (the PHP side, not the templates), you'll need to manually 
clean the content of the dynamic bundles. You can either execute `rm -Rf src/GravitonDyn` or call the command 
`php app/console graviton:clean:dynamicbundles` - to simplify things, you can put those in `dev-cleanstart.sh` during your work.
