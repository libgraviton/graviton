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
php app/console graviton:generate:resource --entity=GravitonFooBundle:Bar --format=xml --fields="name:string" --with-repository
php app/console graviton:generate:resource --entity=GravitonFooBundle:Baz --format=xml --fields="name:string isTrue:boolean consultant:Graviton\\PersonBundle\\Document\\Consultant" --with-repository
```
You will need to clean up the models Resource/schema/<name>.json file after generation. You may replace titles and you must
add descriptions in the fields marked @todo.

### Generate a Dynamic Bundle

```bash
php app/console graviton:generate:dynamicbundle --json
```

@todo wat do?
