<?php
/**
 * GravitonRestBundle
 */

namespace Graviton\RestBundle;

use Graviton\RestBundle\DependencyInjection\Compiler\RestrictionListenerCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use JMS\SerializerBundle\JMSSerializerBundle;
use Graviton\RestBundle\DependencyInjection\Compiler\RestServicesCompilerPass;

/**
 * GravitonRestBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonRestBundle extends Bundle
{

    /**
     * load compiler pass rest route loader
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RestServicesCompilerPass);
        $container->addCompilerPass(new RestrictionListenerCompilerPass());
    }

    /**
     * boot hook
     *
     * @return void
     */
    public function boot()
    {
        // add schema format validator
        // stricter uri format

        $uriValidator = function ($value): bool {
            return (filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) !== null);
        };

        \League\OpenAPIValidation\Schema\TypeFormats\FormatsContainer::registerFormat(
            'string',
            'uri',
            $uriValidator
        );
    }
}
