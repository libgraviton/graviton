<?php
/**
 * A service providing some core util functions.
 */

namespace Graviton\CoreBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreUtils
{
    /**
     * @var string absolute path to cache directory
     */
    private $cacheDir;

    /**
     * @param string $cacheDir string path to cache directory
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Reads the package versions from the cache
     *
     * @return string version
     */
    public function getVersion()
    {
        //@todo if we're in a wrapper context, use the version of the wrapper, not graviton
        $versionFilePath = $this->cacheDir . '/swagger/versions.json';

        if (file_exists($versionFilePath)) {
            $versions = json_decode(file_get_contents($versionFilePath), true);

            if (JSON_ERROR_NONE === json_last_error()) {
                $versionHeader = '';
                foreach ($versions as $name => $version) {
                    $versionHeader .= $name . ': ' . $version. '; ';
                }

                return $versionHeader;

            } else {
                $message = sprintf(
                    'Unable to extract versions from versions.json file (Error code: %s)',
                    json_last_error()
                );

                throw new \RuntimeException($message);
            }
        }
    }
}
