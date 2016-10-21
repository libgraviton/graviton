<?php
/**
 * LoaderFactory
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Exception\LoaderException;

/**
 * Class LoaderFactory
 *
 * @package Graviton\ProxyBundle\Definition\Loader
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoaderFactory
{
    /** @var array  */
    private $loader;


    /**
     * LoaderFactory constructor.
     *
     * @param array $loader The set of definition loaders available.
     */
    public function __construct(array $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Provides a list of registered loaders
     *
     * @return array
     */
    public function getLoaderDefinitions()
    {
        return $this->loader;
    }

    /**
     * Provides
     *
     * @param string $source Information of what loader to be initiated.
     *
     * @return LoaderInterface
     *
     * @throws LoaderException
     */
    public function create($source)
    {
        if (array_key_exists($source, $this->loader)) {
            return $this->loader[$source];
        }

        throw new LoaderException('Expected Loader for source ('. $source .') does not exist.');
    }
}
