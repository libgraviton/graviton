<?php
/**
 * Registry
 */

namespace Graviton\ProxyBundle\Service\Source;

use Graviton\ProxyBundle\Exception\RegistryException;

/**
 * Class Registry
 *
 * @package Graviton\ProxyBundle\Service\Source
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class Registry
{
    /** @var array  */
    public $sources = [];

    /**
     * Adds a proxy source to the registry.
     *
     * @param SourceInterface $source The source to be registered.
     * @param string          $alias  The identifier the source shall be find by.
     *
     * @return Registry
     */
    public function add(SourceInterface $source, $alias)
    {
        $this->sources[$alias] = $source;

        return $this;
    }

    /**
     * Finds the source identified by the given id in the registry;
     *
     * @param string $name Identifier of the source to be returned.
     *
     * @return SourceInterface
     */
    public function get($name)
    {
        if (!empty($this->sources[$name])) {
            return $this->sources[$name];
        }

        throw new RegistryException('There is no source registered matching the given identifyer ('. $name .')');
    }

    /**
     * Indicates if the given name represents a registered source.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return in_array($name, $this->sources);
    }
}
