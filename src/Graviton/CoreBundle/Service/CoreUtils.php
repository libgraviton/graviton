<?php
/**
 * A service providing some core util functions.
 */

namespace Graviton\CoreBundle\Service;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CoreUtils
{
    const X_VERSION_DEFAULT = "0.1.0-alpha";

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
     *      *
     * @todo don't find the composer file like so, use packagist to find and parse it if possible
     * @todo if we're in a wrapper context, use the version of the wrapper, not graviton
     *
     * @return string version
     */
    public function getVersion()
    {
        $version = self::X_VERSION_DEFAULT;
        $composerFile = __DIR__ . '/../../../../composer.json';

        if (file_exists($composerFile)) {

            $composer = json_decode(file_get_contents($composerFile), true);

            if (JSON_ERROR_NONE === json_last_error() && !empty($composer['version'])) {
                $version = $composer['version'];
            } else {
                $message = sprintf(
                    'Unable to extract version from composer.json file: %s (%s)',
                    json_last_error_msg(),
                    json_last_error()
                );

                throw new \RuntimeException($message);
            }
        }

        return $version;
    }
}
