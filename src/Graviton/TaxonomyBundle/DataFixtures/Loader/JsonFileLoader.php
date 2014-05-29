<?php

namespace Graviton\TaxonomyBundle\DataFixtures\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Load json Files that we want to load as fixture.
 *
 * @category GravitonTaxonomyBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class JsonFileLoader extends FileLoader
{
    /**
     * {@inheritDoc}
     *
     * @param String $resource path to file we want to load
     * @param String $type     type of file should be json in all cases
     *
     * @return String
     */
    public function load($resource, $type = null)
    {
        return file_get_contents($resource);
    }

    /**
     * {@inheritDoc}
     *
     * @param String $resource path to a file
     * @param String $type     unused
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
