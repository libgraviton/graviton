<?php
/**
 * compiler pass for building a listing of fields for compiler
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormFieldsCompilerPass implements CompilerPassInterface, LoadFieldsInterface
{
    use LoadFieldsTrait;

    /**
     * @var array
     */
    private $serviceMap;

    /**
     * @var array
     */
    private $typeMap = [
        'string' => 'text',
        'extref' => 'extref',
        'int' => 'integer',
        'float' => 'number',
        'boolean' => 'checkbox',
        'date' => 'datetime',
    ];

    /**
     * @var string
     */
    private $className;

    /**
     * load services
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    final public function process(ContainerBuilder $container)
    {
        $this->serviceMap = (array) $container->getParameter(
            'graviton.document.form.type.document.service_map'
        );
        $gravitonServices = $container->findTaggedServiceIds(
            'graviton.rest'
        );
        $map = [];
        foreach ($gravitonServices as $id => $tag) {
            list($ns, $bundle,, $doc) = explode('.', $id);
            if (empty($bundle) || empty($doc)) {
                continue;
            }
            if ($bundle == 'core' && $doc == 'main') {
                continue;
            }
            list($doc, $bundle) = $this->getInfoFromTag($tag, $doc, $bundle);

            $this->className  = $container->getParameter(
                substr(
                    substr(
                        $this->serviceMap[strtolower(implode('.', [$ns, $bundle, 'controller', $doc]))],
                        1
                    ),
                    0,
                    -1
                )
            );
            $this->loadFields($map, $ns, $bundle, $doc);
            $this->className = null;
        }
        $container->setParameter('graviton.document.form.type.document.field_map', $map);
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
        $fieldNodes = $xpath->query("//doctrine:field");

        $class = $this->className;
        if ($name !== '') {
            $class = $name;
        }

        $map[$class] = [];
        foreach ($fieldNodes as $node) {
            $fieldName = $node->getAttribute('fieldName');
            $doctrineType = $node->getAttribute('type');

            $translatableFields = [];
            if (in_array(
                'Graviton\I18nBundle\Document\TranslatableDocumentInterface',
                array_keys(class_implements($this->className))
            )) {
                $fieldInstance = new $this->className;
                $translatableFields = $fieldInstance->getTranslatableFields();
            }

            $type = 'text';
            $options = [];
            if (in_array($fieldName, $translatableFields)) {
                $type = 'translatable';
            } elseif ($doctrineType == 'hash') {
                $type = 'form';
                $options['allow_extra_fields'] = true;
            } elseif (array_key_exists($doctrineType, $this->typeMap)) {
                $type = $this->typeMap[$doctrineType];
            }
            $map[$class][] = [$fieldName, $type, $options];
        }
        $embedNodes = $xpath->query("//doctrine:embed-one");
        foreach ($embedNodes as $node) {
            $fieldName = $node->getAttribute('field');
            $targetDocument = $node->getAttribute('target-document');

            $this->loadEmbeddedDocuments(
                $map,
                $xpath->query("//doctrine:embed-one[@field='".$fieldName."']"),
                $targetDocument
            );
            $map[$class][] = [$fieldName, 'form', ['data_class' => $targetDocument]];
        }
        $embedNodes = $xpath->query("//doctrine:embed-many");
        foreach ($embedNodes as $node) {
            $fieldName = $node->getAttribute('field');
            $targetDocument = $node->getAttribute('target-document');

            $this->loadEmbeddedDocuments(
                $map,
                $xpath->query("//doctrine:embed-many[@field='".$fieldName."']"),
                $targetDocument,
                true
            );
            $map[$class][] = [
                $fieldName,
                'collection',
                [
                    'type' => 'form',
                    'options' => [
                        'data_class' => $targetDocument
                    ]
                ]
            ];
        }
    }
}
