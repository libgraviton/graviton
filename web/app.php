<?php
/**
 * main entrypoint for graviton; env dependent
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */

use Graviton\AppKernel;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// @codingStandardsIgnoreStart
$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
// @codingStandardsIgnoreEnd

require_once __DIR__.'/../app/AppKernel.php';

// check for env
$env = getenv('SYMFONY_ENV');
if (false === $env) {
    // fallback to 'prod'
    $env = 'prod';
}

$activateDebug = false;
if (strpos($env, 'dev') !== false) {
    // catch also oauth_dev & co..
    $activateDebug = true;
}

if ($activateDebug) {
    Debug::enable();
}
$kernel = new AppKernel($env, $activateDebug);

$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));
$kernel->loadClassCache();

//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller
// instead of relying on the configuration parameter
// Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
