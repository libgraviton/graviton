<?php

namespace Graviton\EntityBundle\DataFixtures\Loader;

use Symfony\Component\Config\Loader\FileLoader;

/**
 * Load json Files that we want to load as fixture.
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
