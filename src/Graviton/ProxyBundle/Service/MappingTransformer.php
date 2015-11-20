<?php
/**
 * MappingTransformer
 */

namespace Graviton\ProxyBundle\Service;

use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * This class transforms objects / arrays by applying a mapping on them.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MappingTransformer
{

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param PropertyAccessor $propertyAccessor The Symfony PropertyAccessor for reading / writing the objects / arrays
     */
    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Applies the given mapping on a given object or array.
     *
     * @param object|array $raw The input object or array
     * @param array $mapping The mapping
     * @param object|array $transformed The output object or array.
     * @return array
     */
    public function transform($raw, array$mapping, $transformed = [])
    {
        foreach ($mapping as $destination => $source) {
            $value = $this->propertyAccessor->isReadable($raw, $source) ?
                $this->propertyAccessor->getValue($raw, $source) : null;
            $this->propertyAccessor->setValue($transformed, $destination, $value);
        }
        return $transformed;
    }

}