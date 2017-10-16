# Installation

## Step 1: Download the Bundle

This bundle is part of the graviton library.

## Step 2: Enable the Bundle

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

## Step 3: Configuration

### Authentication

Graviton supports multiple Authentication schemes, each of one can be activated by switching to a corresponding Symfony
environment.

#### Model

The authentication con be configured to use custom tables in order to be valid for any possible environment.
Using the configuration parameters you can either decide if there shall be security or not to access the service.
 
 Using Symfony's security.token_storage you can get the authenticated user if so enabled, 
 
Required, controls if headers should be sent or not. If option is set true, it will check if in request the key is set.
If no key then a AuthenticationException will be thrown even before DB query. 
```yml
parameters:
    graviton.security.authentication.header_required: <BOOLEAN>
```

If in DB there is a option to run a test user on each request without using headers, add here the identifier to find 
that test user. 
```yml
parameters:
    graviton.security.authentication.test_username: <FALSE or string username >
```

To use an Empty anonymous user we can have this option on True, so even if no test test user nor header requested user 
is found we can choose if we wish to throw a AuthenticationException. 
```yml
parameters:
    graviton.security.authentication.allow_anonymous: <BOOLEAN>
```

Type of authentication to be used, cookie or header. 
```yml
parameters:
    graviton.security.authentication.strategy: <SERVICE_ID>
```

Kay value sent in headers or cookie strategy to match and return so db can be queried. 
```yml
parameters:
    graviton.security.authentication.strategy_key: <string>
```

Data model repository to be used to query table to find the user, and test user. 
```yml
parameters:
    graviton.security.authentication.provider.model: <SERVICE_ID>
```

Data model will be queried by this field, if false or empty query will be ignored. 
```yml
parameters:
    graviton.security.authentication.provider.model.query_field: <false | string>
```

MultiStrategy allows several strategies to be loaded. When first strategy maches a "username"
it will return that to be used by provider. If configured to use Multi but no services is defined 
by default all tagged services will be loaded (back-compatibility): `graviton.security.authenticationkey.finder`
```yml
parameters:
    graviton.security.authentication.strategy.multi.services: 
      - <SERVICE_ID:strategy>
```

**NOTE**:
The service referenced in the parameter must implement the »\Graviton\RestBundle\Model\ModelInterface«.

### SecurityUser

Only two roles are implemented, the authenticated user and anonymous:
ROLE_GRAVITON_USER
ROLE_GRAVITON_ANONYMOUS


Example:

```php
/** @var Graviton\SecurityBundle\Entities\SecurityUser $securityUser */
$securityUser = $this->container->get('security.token_storage')
    ->getToken()
    ->getUser();
    
if ( $securityUser ) {
    /** @var YourCustomObjectUser $user */
    $user = $securityUser->getUser();
}

// Symfony is granted by role, 
if ($this->isGranted('ROLE_ADMIN')) { ... }

// Use SecurityUtils for a simpler more clean way to get user information. Injected example.
if ($this->securityUtils->isSecurityUser()) {
    return $this->securityUtils->getSecurityUsername();
}

```


### TODO and verify.
```php
$authorizationChecker = $this->container->get('graviton_security_authenticator');
  
// $request received from ParameterConverter of the action
if (false === $authorizationChecker->isGranted('VIEW', $user)) {
    throw new AccessDeniedException('You are not allowed to be here.');
}  
```


- ServiceAllowedVoter
Acting on the Request object (Symfony\Component\HttpFoundation\Request) this voter determines depending
on a configured whitelist (graviton.security.services.whitelist), if a service may be called or not.

```yml
parameters: 
  graviton.security.services.whitelist: 
    main: /
    core: /core/app
    products: /core/product 
```

Example:

```php

  $authorizationChecker = $this->container->get('security.authorization_checker');

  // $request received from ParameterConverter of the action.
  if (false === $authorizationChecker->isGranted('VIEW', $request)) {
    throw new AccessDeniedException('You are not allowed to be here.');
  }  
```

# Dependencies

- \Graviton\CoreBundle\Repository\AppRepository
- \Graviton\RestBundle\Model\ModelInterface ( this should be resolved asap)
- \Symfony\Component\Security\Core\User\UserInterface

# Future things

- add command to find out what strategies are available.
