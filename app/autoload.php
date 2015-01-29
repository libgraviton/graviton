<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

ini_set('include_path', __DIR__.'/../:'.ini_get('include_path'));

/**
 * @var ClassLoader $loader
 */
$loader = include 'vendor/autoload.php';

if ($loader === false) {
    $loader = include __DIR__.'/../../../autoload.php';
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
