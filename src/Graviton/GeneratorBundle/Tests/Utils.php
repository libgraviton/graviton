<?php
/**
 * simple centralized utils for generator bundle testing
 */

namespace Graviton\GeneratorBundle\Tests;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use JMS\Serializer\SerializerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Utils
{

    /**
     * returns a definition instance from a json definition file
     *
     * @param string $file filename
     *
     * @return JsonDefinition definition
     */
    public static function getJsonDefinition($file)
    {
        $serializer = self::getSerializerInstance();

        return new JsonDefinition(
            $serializer->deserialize(
                file_get_contents($file),
                'Graviton\\GeneratorBundle\\Definition\\Schema\\Definition',
                'json'
            )
        );
    }

    /**
     * returns a serializer instance
     *
     * @return \JMS\Serializer\Serializer serializer
     */
    public static function getSerializerInstance()
    {
        return SerializerBuilder::create()
            ->addDefaultHandlers()
            ->addDefaultSerializationVisitors()
            ->addDefaultDeserializationVisitors()
            ->addMetadataDir(__DIR__.'/../Resources/config/serializer', 'Graviton\\GeneratorBundle')
            ->setDebug(true)
            ->build();
    }
}
