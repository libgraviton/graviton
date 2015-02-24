<?php

namespace Graviton\CoreBundle\Service;

/**
 * A service providing some core util functions.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CoreUtils
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * sets the container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer($container = null)
    {
        $this->container = $container;
    }

    /**
     * Gets the current version we're running on..
     *
     * @return string version
     */
    public function getVersion()
    {
        /**
         * @todo don't find the composer file like so, use packagist to find and parse it if possible
         * @todo if we're in a wrapper context, use the version of the wrapper, not graviton
         */
        $composerFile = __DIR__.'/../../../../composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        return $composer['version'];
    }
}
