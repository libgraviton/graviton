<?php
/**
 * compilerpass for restricted fields
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionFieldsCompilerPass implements CompilerPassInterface
{
    /**
     * @var DocumentMap
     */
    private $documentMap;

    /**
     * prepare a map of base data restrictions for Restriction\Manager
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->documentMap = $container->get('graviton.document.map');

        $map = [];
        foreach ($this->documentMap->getDocuments() as $document) {
            $restrictionFields = $this->documentMap->getFieldNamesFlat(
                $document,
                '',
                '',
                function ($field) {
                    return $field->getRestrictions();
                },
                true
            );

            $finalRestrictionFields = [];
            foreach ($restrictionFields as $key => $field) {
                // cleanup name! .0. has to be .. for rql!
                $key = preg_replace('@\.([0-9]+)\.@i', '..', $key);
                $finalRestrictionFields[$key] = $field->getRestrictions();
            }

            $map[$document->getClass()] = $finalRestrictionFields;
        }

        $container->setParameter('graviton.document.restriction.fields', $map);
    }
}
