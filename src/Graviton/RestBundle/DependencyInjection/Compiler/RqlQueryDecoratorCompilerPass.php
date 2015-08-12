<?php
/**
 * RqlQueryDecoratorCompilerPass class file
 */

namespace Graviton\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryDecoratorCompilerPass implements CompilerPassInterface
{
    /**
     * We have to manually copy all tags from decorated service
     *
     * @param ContainerBuilder $container Container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $innerDefinition = $container->getDefinition('graviton.rest.listener.rqlqueryrequestlistener.inner');
        $outerDefinition = $container->getDefinition('graviton.rest.listener.rqlqueryrequestlistener');

        foreach ($innerDefinition->getTags() as $name => $attrsList) {
            foreach ($attrsList as $attrs) {
                $outerDefinition->addTag($name, $attrs);
            }
        }
        $innerDefinition->clearTags();
    }
}
