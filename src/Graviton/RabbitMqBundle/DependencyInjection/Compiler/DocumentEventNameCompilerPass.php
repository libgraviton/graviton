<?php
/**
 * build a collection of document classes and their possible event names they generate (for document.* namespace)
 */

namespace Graviton\RabbitMqBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentEventNameCompilerPass implements CompilerPassInterface
{
    /**
     * create mapping from services
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = [];

        // this is an excellent basemap
        $extRefMapping = $container->getParameter('graviton.document.type.extref.mapping');

        foreach ($extRefMapping as $documentName => $baseRouteName) {

            list(, $bundle, , $document) = explode('.', $baseRouteName);

            $baseRoutingKey = 'document.'.
                $bundle.
                '.'.
                $document;

            $map[$documentName] = [
                'baseRoute' => $baseRouteName,
                'events' => [
                    'put' => $baseRoutingKey.'.update',
                    'post' => $baseRoutingKey.'.create',
                    'delete' => $baseRoutingKey.'.delete',
                ]
            ];
        }

        $map = $container->setParameter('graviton.eventmap.document', $map);

    }
}
