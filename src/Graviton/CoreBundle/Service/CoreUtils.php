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
     * Gets the current version we're running on..
     *
     * @param string $composerFile Absolute path to the json file providing version information.
     *
     * @return string version
     */
    public function getVersion($composerFile = '')
    {
        //@todo if we're in a wrapper context, use the version of the wrapper, not graviton
        $composerFile = !empty($composerFile) ? $composerFile : __DIR__ . '/../../../../composer.json';

        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);

            if (JSON_ERROR_NONE === json_last_error() && !empty($composer['version'])) {
                return $composer['version'];
            } else {
                $message = sprintf(
                    'Unable to extract version from composer.json file (Error code: %s)',
                    json_last_error()
                );

                throw new \RuntimeException($message);
            }
        }
    }
}
