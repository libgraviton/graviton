<?php
/**
 * build a list of all services that have extref mappings
 *
 * This list later gets used during rendering URLs in the output where we
 * need to know when and wht really needs rendering after our doctrine
 * custom type is only able to spit out the raw data during hydration.
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefFieldsCompilerPass implements CompilerPassInterface
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
        $map = [];
        $gravitonServices = array_filter(
            $container->getServiceIds(),
            function ($id) {
                return substr($id, 0, 8) == 'graviton' &&
                    strpos($id, 'controller') !== false &&
                    $id !== 'graviton.rest.controller';
            }
        );
        foreach ($gravitonServices as $id) {
            list($ns, $bundle,, $doc) = explode('.', $id);
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }

            $file = implode(
                '/',
                [
                    __DIR__,
                    '..',
                    '..',
                    '..',
                    '..',
                    ucfirst($ns),
                    ucfirst($bundle).'Bundle',
                    'Resources',
                    'config',
                    'doctrine',
                    ucfirst($doc).'.mongodb.xml'
                ]
            );

            if (!file_exists($file)) {
                continue;
            }

            $dom = new \DOMDocument;
            $dom->Load($file);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');
            $fieldNodes = $xpath->query("//doctrine:field[@type='extref']");

            $fields = [];
            foreach ($fieldNodes as $node) {
                $fields[] = $node->getAttribute('fieldName');
            }
            
            $map[implode('.', [$ns, $bundle, 'rest', $doc, 'get'])] = $fields;
            $map[implode('.', [$ns, $bundle, 'rest', $doc, 'all'])] = $fields;
        }
        $container->setParameter('graviton.document.type.extref.fields', $map);
    }
}
