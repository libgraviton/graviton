<?php

namespace Graviton\TaxonomyBundle\DataFixtures\Loader;

use Symfony\Component\Config\Loader\FileLoader;

class JsonFileLoader extends FileLoader
{
    public function load($resource, $type = null)
    {
        return file_get_contents($resource);
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'json' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
