<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\AppKernel;

ini_set('include_path', __DIR__.'/../:'.ini_get('include_path'));

$loader = include_once 'app/bootstrap.php.cache';
Debug::enable();

require_once 'app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
