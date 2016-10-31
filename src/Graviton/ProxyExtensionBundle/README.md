# ProxyExtensionBundle
The Graviton Proxy Extension Bundle provides addons (a dedicated set of services (e.g. Fundinfo)) for the Proxy Bundle.

## Prerequisits
- [ProxyBundle](https://github.com/libgraviton/graviton/tree/develop/src/Graviton/ProxyBundle) installed and activated
- Working Internet connection

## Install Extensions
The installation does contain a number of step to be followed. The sequence id not crucial, but the ```dev-cleanstart.sh``` script has to be called
as last step.

### Composer
```bash
$> composer require <extension-bundle>
```

### Add to set of loaded bundles
- change in to wrapper
- open `resources/configuration.sh`
- add the full namespace of your bundle to the list of bundles; be aware of the escaping.

### Configure extension
correspond the README.md of the extension bundle to be added.

### Run wrapper install script

```bash
$> ./dev-cleanstart.sh
```

The script will set up the wrapper to a fully functional backend.

## Available extensions
- [Vontobel](https://git.swisscom.ch/projects/GRV/repos/graviton-service-bundle-proxy-vontobel/browse)
- [ZugerKB](https://git.swisscom.ch/projects/GRV/repos/graviton-service-bundle-proxy-zugerkb/browse)
- [Fundinfo](https://git.swisscom.ch/projects/GRV/repos/graviton-service-bundle-proxy-fundinfo/browse)
