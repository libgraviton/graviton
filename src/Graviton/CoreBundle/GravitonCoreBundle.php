<?php
/**
 * core infrastructure like logging and framework.
 */

namespace Graviton\CoreBundle;

use Graviton\CoreBundle\Compiler\EnvParametersCompilerPass;
use Graviton\CoreBundle\Compiler\HttpClientOptionsCompilerPass;
use Graviton\CoreBundle\Compiler\RouteLoaderCompilerPass;
use Jean85\PrettyVersions;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\CoreBundle\Compiler\VersionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * GravitonCoreBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonCoreBundle extends Bundle
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

        $container->addCompilerPass(new VersionCompilerPass(new PrettyVersions()));
        $container->addCompilerPass(new EnvParametersCompilerPass());
        $container->addCompilerPass(new RouteLoaderCompilerPass());
        $container->addCompilerPass(new HttpClientOptionsCompilerPass());
    }
}
