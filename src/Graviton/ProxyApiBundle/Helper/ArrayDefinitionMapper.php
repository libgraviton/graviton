<?php
/**
 * Part of ArrayMapper
 */
namespace Graviton\ProxyApiBundle\Helper;

use Graviton\ProxyApiBundle\Listener\ProxyExceptionListener;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Automatically map ARRAY structures into objects.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayDefinitionMapper
{
    /**
     * Maps data into object class
     *
     * @param object $array  to be casted
     * @param object $object Class to receive data
     * @throws ProxyExceptionListener
     * @return object
     */
    public function map($array, $object)
    {
        if (!is_array($array)) {
            throw new ProxyExceptionListener(
                422,
                'JsonMapper::map() requires first argument to be an Array'
                . ', ' . gettype($array) . ' given.'
            );
        }
        if (!($object instanceof Definition)) {
            throw new ProxyExceptionListener(
                422,
                'JsonMapper::map() requires second argument to be a Definition'
                . ', ' . gettype($object) . ' given.'
            );
        }

        foreach ($array as $key => $value) {
            $key = $this->getSafeName($key);
            $setter = 'set' . $this->getCamelCaseName($key);
            $object->addMethodCall($setter, [$value]);
        }

        return $object;
    }

    /**
     * Removes - and _ and makes the next letter uppercase
     *
     * @param string $name Property name
     *
     * @return string CamelCasedVariableName
     */
    protected function getCamelCaseName($name)
    {
        return str_replace(
            ' ', '', ucwords(str_replace(array('_', '-'), ' ', $name))
        );
    }

    /**
     * Since hyphens cannot be used in variables we have to uppercase them.
     *
     * @param string $name Property name
     *
     * @return string Name without hyphen
     */
    protected function getSafeName($name)
    {
        if (strpos($name, '-') !== false) {
            $name = $this->getCamelCaseName($name);
        }

        return $name;
    }
}
