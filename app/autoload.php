<?php
/**
 * autoloader config for graviton
 *
 * @category Graviton
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * don't touch this unless you know what you're doing..
 * basically we have two autoloaders provided by Composer.
 * 1) the only one when we are the main project (and we have a vendor/)
 * 2) the one we're *we* are a dependency and we need the upper autoload.php
 */

$primaryLoader = __DIR__.'/../vendor/autoload.php';
$secondaryLoader = __DIR__.'/../../../autoload.php';

// @codingStandardsIgnoreStart
if (file_exists($primaryLoader)) {
    $loader = require $primaryLoader;
} else {
    $loader = require $secondaryLoader;
}
// @codingStandardsIgnoreEnd

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
