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
class ExtRefFieldsCompilerPass extends AbstractExtRefCompilerPass implements LoadFieldsInterface
{
    /**
     * @see \Graviton\DocumentBundle\DependencyInjection\Compiler\LoadFieldsTrait
     */
    use LoadFieldsTrait;

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
        $map = [];
        foreach ($services as $id) {
            list($ns, $bundle,, $doc) = explode('.', $id);
            if (empty($bundle) || empty($doc)) {
                continue;
            }
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }
            $tag = $container->getDefinition($id)->getTag('graviton.rest');
            list($doc, $bundle) = $this->getInfoFromTag($tag, $doc, $bundle);
            $this->loadFields($map, $ns, $bundle, $doc);
        }
        $container->setParameter('graviton.document.type.extref.fields', $map);
    }

    /**
     * @param array     $map      map to add entries to
     * @param \DOMXPath $xpath    xpath access to doctrine config dom
     * @param string    $ns       namespace
     * @param string    $bundle   bundle name
     * @param string    $doc      document name
     * @param boolean   $embedded is this an embedded doc, further args are only for embeddeds
     * @param string    $name     name prefix of document the embedded field belongs to
     * @param string    $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    public function loadFieldsFromDOM(
        array &$map,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    ) {
        $fieldNodes = $xpath->query("//doctrine:field[@type='extref']");

        $fields = [];
        foreach ($fieldNodes as $node) {
            $fields[] = '$'.$node->getAttribute('fieldName');
        }

        $namePrefix = strtolower(implode('.', [$ns, $bundle, 'rest', $doc, '']));

        $this->loadEmbeddedDocuments(
            $map,
            $xpath->query('//*[self::doctrine:embed-one or self::doctrine:reference-one]'),
            $namePrefix
        );
        $this->loadEmbeddedDocuments(
            $map,
            $xpath->query('//*[self::doctrine:embed-many or self::doctrine:reference-many]'),
            $namePrefix,
            true
        );

        foreach (['get', 'all'] as $suffix) {
            if ($embedded) {
                $mapName = $name.$suffix;
            } else {
                $mapName = $namePrefix.$suffix;
            }
            if (empty($map[$mapName])) {
                $map[$mapName] = [];
            }
            if ($embedded) {
                foreach ($fields as $field) {
                    $map[$mapName][] = $prefix.$field;
                }
            } else {
                $map[$mapName] = array_merge($fields, $map[$mapName]);
            }
        }

        // make em all unique
        foreach ($map as $name => $fields) {
            $map[$name] = array_unique($fields);
        }
    }
}
