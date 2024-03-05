<?php
/**
 * Part of JsonMapper
 */
namespace Graviton\AnalyticsBundle\Helper;

use Psr\Log\InvalidArgumentException;

/**
 * Automatically map JSON structures into objects.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonMapper
{
    /**
     * Maps data into object class
     *
     * @param object $json   to be casted
     * @param object $object Class to receive data
     * @throws InvalidArgumentException
     * @return object
     */
    public function map($json, $object)
    {
        foreach ($json as $key => $jvalue) {
            $key = $this->getSafeName($key);
            $setter = 'set' . $this->getCamelCaseName($key);
            if (method_exists($object, $setter)) {
                $object->{$setter}($jvalue);
            }
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
    protected function getCamelCaseName($name): string
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
    protected function getSafeName($name): string
    {
        if (strpos($name, '-') !== false) {
            $name = $this->getCamelCaseName($name);
        }

        return $name;
    }
}
