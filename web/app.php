<?php
/**
 * main entrypoint for graviton (forced production)
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */

//use Symfony\Component\ClassLoader\ApcClassLoader;

// @codingStandardsIgnoreStart
$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
// @codingStandardsIgnoreEnd

// Enable APC for autoloading to improve performance.
// You should change the ApcClassLoader first argument to a unique prefix
// in order to prevent cache key conflicts with other applications
// also using APC.
/*
$apcLoader = new ApcClassLoader(sha1(__FILE__), $loader);
$loader->unregister();
$apcLoader->register(true);
*/

putenv('SYMFONY_ENV=prod');
require_once 'app_env.php';
