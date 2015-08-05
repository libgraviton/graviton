<?php
/**
 * build a list of all services that have extref mappings
 *
 * This list later gets used during rendering URLs in the output where we
 * need to know when and wht really needs rendering after our doctrine
 * custom type is only able to spit out the raw data during hydration.
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefFieldsCompilerPass extends AbstractDocumentFieldCompilerPass
{
    /**
     * @var array Doctrine mappings
     */
    protected $classMap = [];

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     * @param array            $services  services to inspect
     *
     * @return void
     */
    public function processServices(ContainerBuilder $container, $services)
    {
        $this->classMap = $this->loadDoctrineClassMap();

        $map = [];
        foreach ($services as $id) {
            list($ns, $bundle, , $doc) = explode('.', $id);
            if (empty($bundle) || empty($doc)) {
                continue;
            }
            if ($bundle === 'core' && $doc === 'main') {
                continue;
            }

            $className = $this->getServiceDocument(
                $container->getDefinition($id),
                $ns,
                $bundle,
                $doc
            );
            $extRefFields = $this->processDocument($className);
            $routePrefix = strtolower($ns.'.'.$bundle.'.'.'rest'.'.'.$doc);

            $map[$routePrefix.'.get'] = $extRefFields;
            $map[$routePrefix.'.patch'] = $extRefFields;
            $map[$routePrefix.'.all'] = $extRefFields;
        }

        $container->setParameter('graviton.document.type.extref.fields', $map);
    }


    /**
     * Get document $extref fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    protected function filterDocumentFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        return array_map(
            function (\DOMElement $node) {
                return '$'.$node->getAttribute('fieldName');
            },
            iterator_to_array(
                $xpath->query('//doctrine:field[@type="extref"]')
            )
        );
    }
}
