<?php

use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$loader = include_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new \Graviton\AppKernel('prod', false);
//$kernel = new AppCache($kernel);

// make sure graviton bundles are found and initialized
$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
