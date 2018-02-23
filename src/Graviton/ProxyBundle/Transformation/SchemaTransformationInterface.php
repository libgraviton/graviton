<?php
/**
 * SchemaTransformation
 */

namespace Graviton\ProxyBundle\Transformation;

/**
 * This class interface should be used by transformers transforming JSON schemas.
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface SchemaTransformationInterface
{

    /**
     * Transforms a schema
     *
     * @param  \stdClass $schemaIn  The original schema object
     * @param  \stdClass $schemaOut The schema object to transform
     * @return null|\stdClass The returned stdClass schema will be used as $schemaOut for following transformations.
     * If you do not return any schema, the same $schemaOut instance will be used again.
     */
    public function transformSchema(\stdClass $schemaIn, \stdClass $schemaOut);
}
