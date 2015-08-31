<?php
/**
 * DocumentFormDataMapCompilerPass class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\Document;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedMany;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\EmbedOne;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormDataMapCompilerPass implements CompilerPassInterface
{
    /**
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * Constructor
     *
     * @param DocumentMap $documentMap Document map
     */
    public function __construct(DocumentMap $documentMap)
    {
        $this->documentMap = $documentMap;
    }

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = ['stdclass' => []];
        foreach ($this->documentMap->getDocuments() as $document) {
            $map[$document->getClass()] = $this->getFormDataMap($document);
        }
        $container->setParameter('graviton.document.form.data.map', $map);
    }

    /**
     * Get document fields
     *
     * @param Document $document Document
     * @param string   $prefix   Field prefix
     * @return array
     */
    private function getFormDataMap(Document $document, $prefix = '')
    {
        $result = [];
        foreach ($document->getFields() as $field) {
            if ($field instanceof EmbedOne) {
                $result = array_merge(
                    $result,
                    $this->getFormDataMap($field->getDocument(), $prefix.$field->getExposedName().'.')
                );
            } elseif ($field instanceof EmbedMany) {
                $result = array_merge(
                    $result,
                    $this->getFormDataMap($field->getDocument(), $prefix.$field->getExposedName().'.0.')
                );
            }

            if ($field->getExposedName() !== $field->getFormName()) {
                $result[$prefix.$field->getExposedName()] = $field->getFormName();
            }
        }
        return $result;
    }
}
