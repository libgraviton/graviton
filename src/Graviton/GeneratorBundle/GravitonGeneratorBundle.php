<?php
/**
 * bundle containing various code generators
 */

namespace Graviton\GeneratorBundle;

use Graviton\GeneratorBundle\DependencyInjection\Compiler\GeneratorHashCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * bundle containing various code generators
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonGeneratorBundle extends Bundle
{

    /**
     * load compiler pass
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new GeneratorHashCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            50
        );
    }
}
