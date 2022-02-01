<?php
/**
 * GravitonJsonSchemaBundle class file
 */

namespace Graviton\JsonSchemaBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * JSON schema bundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonJsonSchemaBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * @param ContainerBuilder $container Container builder
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->setParameter(
            'graviton.jsonschema.schema.uri',
            'file://'.__DIR__.'/Resources/schema/loadconfig/v1.0/schema.json'
        );
    }
}
