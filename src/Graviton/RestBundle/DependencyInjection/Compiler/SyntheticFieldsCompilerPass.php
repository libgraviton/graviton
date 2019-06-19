<?php
/**
 * stuff that has to do with synthetic fields
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Graviton\CoreBundle\Util\CoreUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SyntheticFieldsCompilerPass implements CompilerPassInterface
{
    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'graviton.rest.synthetic_fields',
            CoreUtils::parseStringFieldList($container->getParameter('graviton.generator.synthetic_fields'))
        );
    }
}
