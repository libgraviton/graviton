<?php
/**
 * load fields from config
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
trait LoadFieldsTrait
{
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
        $finder = new Finder;
        $files = $finder
            ->files()
            ->in(
                implode(
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
                        'doctrine'
                    ]
                )
            )->name(
                ucfirst($doc).'.mongodb.xml'
            );

        if ($files->count() != 1) {
            return;
        }
        $file = null;
        foreach ($files as $fileObject) {
            $file = $fileObject->getRealPath();
        }
        if (!file_exists($file)) {
            return;
        }

        $dom = new \DOMDocument;
        $dom->Load($file);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('doctrine', 'http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping');

        $this->loadFieldsFromDOM($map, $xpath, $ns, $bundle, $doc, $embedded, $name, $prefix);
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
    abstract public function loadFieldsFromDOM(
        array &$map,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    );

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

    /**
     * get doc/bundle tuple from first tag in collection if available
     *
     * @param array  $tags   array of tags
     * @param string $doc    default doc name
     * @param string $bundle default bundle name
     *
     * @return array
     */
    protected function getInfoFromTag(array $tags, $doc, $bundle)
    {
        if (!empty($tags[0]['collection'])) {
            $doc = $tags[0]['collection'];
            $bundle = ucfirst($tags[0]['collection']);
        }
        return [$doc, $bundle];
    }
}
