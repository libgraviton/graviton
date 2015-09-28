<?php
/**
 * A service providing some core util functions.
 */

namespace Graviton\CoreBundle\Service;

use Graviton\ExceptionBundle\Exception\MissingVersionFileException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreUtils
{
    /**
     * @var array holds all version numbers of installed packages
     */
    private $versions;

    /**
     * @param array $versions Array containing version numbers of installed packages
     */
    public function __construct($versions)
    {
        $this->versions = $versions;
    }

    /**
     * returns versions in response header format
     *
     * @return string version
     */
    public function getVersionInHeaderFormat()
    {
        $versionHeader = '';
        foreach ($this->versions as $name => $version) {
            $versionHeader .= $version['id'] . ': ' . $version['version'] . '; ';
        }

        return $versionHeader;
    }


    /**
     * @return array versions
     */
    public function getVersion()
    {
        $ver = array();
        foreach ($this->versions as $version) {
            $ver[$version['id']]= $version['version'];
        }
        return $ver;
    }

    /**
     *
     * @return array wrapper version
     */
    public function getWrapperVersion()
    {
        foreach ($this->versions as $version) {
            if ($version['id'] === 'self') {
                return $version;
            }
        }
    }
}
