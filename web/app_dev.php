<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Graviton\BundleBundle\Loader\BundleLoader;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\AppKernel;

$loader = include_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
