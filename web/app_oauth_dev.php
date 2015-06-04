<?php
/**
 * main entrypoint for development with oauth activated
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
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('oauth_dev', true);

$kernel->setBundleLoader(new BundleLoader(new GravitonBundleBundle()));

$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
