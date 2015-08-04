<?php
/**
 * abstract compiler pass for document field things
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * An AbstractDocumentFieldsCompilerPass has the focus of somehow filtering/preparing stuff
 * that relate to document fields.. so it at one point receives a DOMDocument of a doctrine
 * definition and return what it concerns there and then do what it wants with it.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractDocumentFieldCompilerPass extends AbstractDocumentCompilerPass
{
    /**
     * @var array Doctrine mappings
     */
    protected $classMap = [];

    /**
     * Filter out whatever you need of the $document
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    abstract protected function filterDocumentFields(\DOMDocument $document);

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
    protected function getServiceDocument(Definition $service, $ns, $bundle, $doc)
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
    protected function processDocument($documentClass, $prefix = '')
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
    protected function loadDoctrineClassMap()
    {
        $classMap = [];
        foreach ($this->getDoctrineMappingFinder() as $file) {
            $document = new \DOMDocument();
            $document->load($file);

            $classMap[$this->getDocumentClassName($document)] = [
                'fields' => $this->filterDocumentFields($document),
                'embedded' => $this->getDocumentEmbeddedDocs($document),
            ];
        }

        return $classMap;
    }

    /**
     * Get document class name
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     *
     * @return string
     */
    protected function getDocumentClassName(\DOMDocument $document)
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
}
