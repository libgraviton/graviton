Installation
============

Step 1: Download the Bundle
---------------------------

This bundle is part of the graviton library.

Step 2: Enable the Bundle
-------------------------

Graviton has it's own way how to register a new bundle in the symfony kernel.
In order to register this bundle it has to be instantiated in the method »\Graviton\CoreBundle\GravitonCoreBundle::getBundles()«.

```php
[...]
   public function getBundles()
    {
        return array(
           [...]
            new \Graviton\SecurityBundle\GravitonSecurityBundle\GravitonSecurityBundle(),
            [...]
        );
    }    

[...]
```

Step 3: Configuration
---------------------

Authentication 
==============

The authentication part of the bundle does provide the ability by changing the way authentication information are
provided by Airlock by configuration. 
The configuration is done by setting the parameter »graviton.security.authentication.strategy« to the class to be used.
 
```yml
parameters:
    graviton.security.authentication.strategy: Graviton\SecurityBundle\Authentication\Strategies\MyApiKeyExtractionStrategy
```

Authorization
=============

tbd


Future things
-------------
- add command to find out what strategies are available.
