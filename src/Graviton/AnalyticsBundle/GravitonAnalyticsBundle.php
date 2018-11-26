<?php
/**
 * Graviton Analytics Bundle
 */

namespace Graviton\AnalyticsBundle;

use Graviton\AnalyticsBundle\Compiler\AnalyticsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonAnalyticsBundle extends Bundle
{
    /**
     * load version compiler pass
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new AnalyticsCompilerPass());
    }
}
