<?php
/**
 * build a list of all services that have translatable mappings
 *
 * this can be used by whoever needs to know where translatables are..
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TranslatableFieldsCompilerPass extends AbstractDocumentFieldCompilerPass
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

            $map[$className] = $this->processDocument($className);
        }

        $container->setParameter('graviton.document.type.translatable.fields', $map);
    }


    /**
     * Get document translatable fields
     *
     * @param \DOMDocument $document Doctrine mapping XML document
     * @return array
     */
    protected function filterDocumentFields(\DOMDocument $document)
    {
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        // get class name
        $className = $this->getDocumentClassName($document);
        if (!class_exists($className)) {
            return [];
        }

        $class = new $className();
        $fields = [];

        if ($class instanceof TranslatableDocumentInterface) {
            $fields = $class->getTranslatableFields();
        }

        return $fields;
    }
}
