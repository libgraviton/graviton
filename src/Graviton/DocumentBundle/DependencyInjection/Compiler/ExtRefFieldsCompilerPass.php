<?php
/**
 * build a list of all services that have extref mappings
 *
 * This list later gets used during rendering URLs in the output where we
 * need to know when and wht really needs rendering after our doctrine
 * custom type is only able to spit out the raw data during hydration.
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtRefFieldsCompilerPass extends AbstractExtRefCompilerPass
{
    /**
     * @var array Doctrine mappings
     */
    private $classMap = [];

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
     * Get base directory for Doctrine XML mappings
     *
     * @return Finder
     */
    protected function getDoctrineMappingFinder()
    {
        return (new Finder())
            ->in(__DIR__ . '/../../../..')
            ->path('Resources/config/doctrine')
            ->name('*.mongodb.xml');
    }

    /**
     * Get document class name from service
     *
     * @param Definition $service Service definition
     * @param string     $ns      Bundle namespace
     * @param string     $bundle  Bundle name
     * @param string     $doc     Document name
     * @return string
     */
    private function getServiceDocument(Definition $service, $ns, $bundle, $doc)
    {
        $tags = $service->getTag('graviton.rest');
        if (!empty($tags[0]['collection'])) {
            $doc = $tags[0]['collection'];
            $bundle = $tags[0]['collection'];
        }

        if (strtolower($ns) === 'gravitondyn') {
            $ns = 'GravitonDyn';
        }

        return sprintf(
            '%s\\%s\\Document\\%s',
            ucfirst($ns),
            ucfirst($bundle).'Bundle',
            ucfirst($doc)
        );
    }

    /**
     * Recursive doctrine document processing
     *
     * @param string $documentClass Document class
     * @param string $prefix        Field prefix
     * @return array
     */
    private function processDocument($documentClass, $prefix = '')
    {
        if (!isset($this->classMap[$documentClass])) {
            return [];
        }

        $result = [];
        foreach ($this->classMap[$documentClass]['fields'] as $field) {
            $result[] = $prefix.$field;
        }
        foreach ($this->classMap[$documentClass]['embedded'] as $field => $embed) {
            $result = array_merge(
                $result,
                $this->processDocument(
                    $embed['class'],
                    $prefix.$field.($embed['multi'] ? '.0.' : '.')
                )
            );
        }

        return $result;
    }

    /**
     * Load doctrine class map
     *
     * @return array
     */
    private function loadDoctrineClassMap()
    {
        $classMap = [];
        foreach ($this->getDoctrineMappingFinder() as $file) {
            $document = new \DOMDocument();
            $document->load($file);

            $classMap[$this->getDocumentClassName($document)] = [
                'fields' => $this->getDocumentExtRefFields($document),
                'embedded' => $this->getDocumentEmbeddedDocs($document),
            ];
        }

        return $classMap;
    }

    /**
     * Get document class name
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return string
     */
    private function getDocumentClassName(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        $node = $xpath->query('//*[self::doctrine:document or self::doctrine:embedded-document]')->item(0);
        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException('Invalid XML mapping file');
        }

        return $node->getAttribute('name');
    }

    /**
     * Get document embedded docs
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    private function getDocumentEmbeddedDocs(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        $result = [];
        foreach ($xpath->query('//*[self::doctrine:embed-one or self::doctrine:reference-one]') as $node) {
            $result[$node->getAttribute('field')] = [
                'class' => $node->getAttribute('target-document'),
                'multi' => false,
            ];
        }
        foreach ($xpath->query('//*[self::doctrine:embed-many or self::doctrine:reference-many]') as $node) {
            $result[$node->getAttribute('field')] = [
                'class' => $node->getAttribute('target-document'),
                'multi' => true,
            ];
        }

        return $result;
    }

    /**
     * Get document $extref fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    private function getDocumentExtRefFields(\DOMDocument $document)
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
