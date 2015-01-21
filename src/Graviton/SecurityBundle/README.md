Installation
============

Step 1: Download the Bundle
---------------------------

This bundle is part of the graviton library.

Step 2: Enable the Bundle
-------------------------

Graviton has it's own way how to register a new bundle in the symfony kernel.
In order to propagate this bundle the method »\Graviton\SecurityBundle\DependencyInjection\GravitonSecurityExtension::getConfigDir()«
has to return the absolute path to the the bundle's configuration directory.

see \Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension for details.

Step 3: Configuration
---------------------

The Graviton SecurityBundle favors xml configuration over the other possibilities.

To configure different event listener strategies use the following template in the environment specific config.xml:
 
 ```xml
     <config xmlns="http://example.org/schema/dic/graviton_security">

        <authentication-service><!-- SERVICE ID OF THE LISTENER STRATEGY--></authentication-service>
    </config>
 ```
