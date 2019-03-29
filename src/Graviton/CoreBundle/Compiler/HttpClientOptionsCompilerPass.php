<?php
/** compiles all necessary guzzle client options in their final form */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HttpClientOptionsCompilerPass implements CompilerPassInterface
{

    /**
     * add guzzle options
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $baseOptions = [
            'verify' => false
        ];

        $container->setParameter(
            'graviton.core.http.client.options',
            $baseOptions
        );
    }
}
