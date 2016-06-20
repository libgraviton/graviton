<?php
/**
 * a CompilerPass to assist the new JSON schema based validation for readOnly fields
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyFieldsCompilerPass implements CompilerPassInterface
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
     * Make readOnly fields map and set it to parameter
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $readOnlyFields = $this->documentMap->getFieldNamesFlat(
                $document,
                '',
                '',
                function ($field) {
                    return $field->isReadOnly();
                }
            );

            if (!empty($readOnlyFields)) {
                foreach ($readOnlyFields as $key => $readOnlyField) {
                    if (substr($readOnlyField, -2) == '.0') {
                        unset($readOnlyFields[$key]);
                    }
                }

                $map[$document->getClass()] = array_values($readOnlyFields);
            }
        }

        $container->setParameter('graviton.document.readonly.fields', $map);
    }
}
