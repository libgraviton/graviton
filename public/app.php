<?php
/**
 * main entrypoint for graviton; env dependent
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
// @codingStandardsIgnoreStart
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
// @codingStandardsIgnoreEnd

use Graviton\AppKernel;
use Graviton\AppCache;
use Graviton\BundleBundle\GravitonBundleBundle;
use Graviton\BundleBundle\Loader\BundleLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

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
    \Symfony\Component\ErrorHandler\Debug::enable();
}
$kernel = new AppKernel($env, $activateDebug);

if (!$activateDebug) {
    $kernel = new AppCache($kernel);
}

Request::setTrustedProxies(
    ['0.0.0.0/0'],
    Request::HEADER_FORWARDED &&
    Request::HEADER_X_FORWARDED_FOR &&
    Request::HEADER_X_FORWARDED_HOST &&
    Request::HEADER_X_FORWARDED_PROTO &&
    Request::HEADER_X_FORWARDED_PORT
);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
