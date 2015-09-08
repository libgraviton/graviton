<?php
/**
 * A service providing some core util functions.
 */

namespace Graviton\CoreBundle\Service;

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
     * @var array holds all version numbers of installed packages
     */
    private $versions;

    /**
     * @param string $cacheDir string path to cache directory
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * returns versions in response header format
     *
     * @return string version
     */
    public function getVersionInHeaderFormat()
    {
        //@todo if we're in a wrapper context, use the version of the wrapper, not graviton

        $versions = $this->getVersion();

        $versionHeader = '';
        foreach ($versions as $name => $version) {
            $versionHeader .= $version->id . ': ' . $version->version . '; ';
        }

        return $versionHeader;
    }

    /**
     * reads versions from versions.json
     *
     * @return void
     */
    private function setVersion()
    {
        $versionFilePath = $this->cacheDir . '/core/versions.json';

        if (file_exists($versionFilePath)) {
            $this->versions = json_decode(file_get_contents($versionFilePath));

        } else {
            throw new \RuntimeException('Unable to find versions.json in cache');
        }

    }

    /**
     * @return array versions
     */
    public function getVersion()
    {
        if ($this->versions) {
            return $this->versions;
        } else {
            $this->setVersion();
            return $this->versions;
        }
    }

    /**
     * @param string $idadd package name
     * @return object single entry
     */
    public function getVersionById($id)
    {
        foreach ($this->getVersion() as $version) {
            if ($version->id == $id) {
                return $version;
            }
        }

    }
}
