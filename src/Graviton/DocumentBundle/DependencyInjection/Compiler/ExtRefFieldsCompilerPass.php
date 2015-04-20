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
class ExtRefFieldsCompilerPass extends AbstractExtRefCompilerPass
{
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
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }
            $this->loadFields($map, $ns, $bundle, $doc);
        }
        $container->setParameter('graviton.document.type.extref.fields', $map);
    }

    /**
     * generate fields from services recursivly
     *
     * @param array   $map      map to add entries to
     * @param string  $ns       namespace
     * @param string  $bundle   bundle name
     * @param string  $doc      document name
     * @param boolean $embedded is this an embedded doc, further args are only for embeddeds
     * @param string  $name     name prefix of document the embedded field belongs to
     * @param string  $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    private function loadFields(&$map, $ns, $bundle, $doc, $embedded = false, $name = '', $prefix = '')
    {
        if (strtolower($ns) === 'gravitondyn') {
            $ns = 'GravitonDyn';
        }
        $file = implode(
            '/',
            [
                __DIR__,
                '..',
                '..',
                '..',
                '..',
                ucfirst($ns),
                ucfirst($bundle).'Bundle',
                'Resources',
                'config',
                'doctrine',
                ucfirst($doc).'.mongodb.xml'
            ]
        );

        if (!file_exists($file)) {
            return;
        }

        $dom = new \DOMDocument;
        $dom->Load($file);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');
        $fieldNodes = $xpath->query("//doctrine:field[@type='extref']");

        $fields = [];
        foreach ($fieldNodes as $node) {
            $fields[] = '$'.$node->getAttribute('fieldName');
        }

        $namePrefix = strtolower(implode('.', [$ns, $bundle, 'rest', $doc, '']));

        $this->loadEmbeddedDocuments($map, $xpath->query('//doctrine:embed-one'), $namePrefix);
        $this->loadEmbeddedDocuments($map, $xpath->query('//doctrine:embed-many'), $namePrefix, true);

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
    }

    /**
     * load fields from embed-* nodes
     *
     * @param array        $map        map to add entries to
     * @param \DomNodeList $embedNodes xpath results with nodes
     * @param string       $namePrefix name prefix of document the embedded field belongs to
     * @param boolean      $many       is this an embed-many relationship
     *
     * @return void
     */
    private function loadEmbeddedDocuments(&$map, $embedNodes, $namePrefix, $many = false)
    {
        foreach ($embedNodes as $node) {
            list($subNs, $subBundle,, $subDoc) = explode('\\', $node->getAttribute('target-document'));
            $prefix = sprintf('%s.', $node->getAttribute('field'));

            // remove trailing Bundle since we are grabbing info from classname and not service id
            $subBundle = substr($subBundle, 0, -6);

            if ($many) {
                $prefix .= '0.';
            }

            $this->loadFields($map, $subNs, $subBundle, $subDoc, true, $namePrefix, $prefix);
        }
    }
}
