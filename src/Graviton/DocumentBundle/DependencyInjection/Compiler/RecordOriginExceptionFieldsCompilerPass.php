<?php
/**
 * a CompilerPass to assist the new JSON schema based validation for recordOriginException fields
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RecordOriginExceptionFieldsCompilerPass implements CompilerPassInterface
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
     * Make recordOriginException fields map and set it to parameter
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $recordOriginExceptionFields = $this->documentMap->getFieldNamesFlat(
                $document,
                '',
                '',
                function ($field) {
                    return $field->isRecordOriginException();
                }
            );

            if (!empty($recordOriginExceptionFields)) {
                foreach ($recordOriginExceptionFields as $key => $recordOriginExceptionField) {
                    if (substr($recordOriginExceptionField, -2) == '.0') {
                        unset($recordOriginExceptionFields[$key]);
                    }
                }

                $map[$document->getClass()] = array_values($recordOriginExceptionFields);
            }
        }

        $container->setParameter('graviton.document.recordoriginexception.fields', $map);
    }
}
