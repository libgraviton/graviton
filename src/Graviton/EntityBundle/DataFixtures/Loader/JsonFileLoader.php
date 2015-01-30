<?php

namespace Graviton\EntityBundle\DataFixtures\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Load json Files that we want to load as fixture.
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class JsonFileLoader extends FileLoader
{
    /**
     * {@inheritDoc}
     *
     * @param string $resource path to file we want to load
     * @param string $type     type of file should be json in all cases
     *
     * @return string
     */
    public function load($resource, $type = null)
    {
        return file_get_contents($resource);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $resource path to a file
     * @param string $type     unused
     *
     * @return Boolean
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'json' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}
