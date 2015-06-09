<?php
/**
 * compiler pass for building a listing of fields for compiler
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\ORM\Mapping\ClassMetadataFactory;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormFieldsCompilerPass implements CompilerPassInterface
{
    /**
     * @var array
     */
    private $serviceMap;

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    final public function process(ContainerBuilder $container)
    {
        $this->serviceMap = $container->getParameter(
            'graviton.document.form.type.document.service_map'
        );
        $gravitonServices = $container->findTaggedServiceIds(
            'graviton.rest'
        );
        $map = [];
        foreach (array_keys($gravitonServices) as $id) {
            list($ns, $bundle,, $doc) = explode('.', $id);
            if (empty($bundle) || empty($doc)) {
                continue;
            }
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }
            $this->loadFields($map, $ns, $bundle, $doc);
        }
        $container->setParameter('graviton.document.form.type.document.field_map', $map);
    }

    use LoadFieldsTrait;

    /**
     * @param array        $map      map to add entries to
     * @param \DOMDOcument $dom      doctrine config dom
     * @param \DOMXPath    $xpath    xpath access to doctrine config dom
     * @param string       $ns       namespace
     * @param string       $bundle   bundle name
     * @param string       $doc      document name
     * @param boolean      $embedded is this an embedded doc, further args are only for embeddeds
     * @param string       $name     name prefix of document the embedded field belongs to
     * @param string       $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    protected function loadFieldsFromDOM(
        array &$map,
        \DOMDocument $dom,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    ) {
        $fieldNodes = $xpath->query("//doctrine:field");

        $className = $this->serviceMap[strtolower(implode('.', [$ns, $bundle, 'controller', $doc]))];
        $map[$className] = [];
        foreach ($fieldNodes as $node) {
            $fieldName = $node->getAttribute('fieldName');

            switch ($node->getAttribute('type')) {
                case 'string':
                    $type = 'text';
                    break;
                default:
                    $type = 'text';
            }
            $map[$className][] = [$fieldName, $type, []];
        }
    }
}
