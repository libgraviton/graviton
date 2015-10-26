<?php
/**
 *
 */
namespace Graviton\GeneratorBundle\Manipulator\File;

use Graviton\GeneratorBundle\Manipulator\ManipulatorException;

/**
 * change the code of a xml file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class XmlManipulator
{
    /** @var string */
    private $options = LIBXML_NOBLANKS;

    /** @var array */
    private $nodes = [];

    /** @var  \DomDocument */
    private $document;

    /**
     * Gathers the provides nodes in a collection to be added to a xml string later.
     *
     * @param string $nodes Xml data to be inserted in to a xml document.
     *
     * @return XmlManipulator
     */
    public function addNodes($nodes)
    {
        if (!empty($nodes)) {
            $this->nodes[] = $nodes;
        }

        return $this;
    }

    /**
     * Resets the current instance
     *
     * @return $this
     */
    public function reset()
    {
        $this->nodes = [];

        return $this;
    }

    /**
     * Renders the gathered nodes into a XML document.
     *
     * @param string $xml Text to be imported to a DomDocument.
     *
     * @return XmlManipulator
     */
    public function renderDocument($xml)
    {
        $this->document = $this->initDomDocument($xml);

        foreach ($this->nodes as $nodeXml) {
            $mergeDoc = $this->initDomDocument($nodeXml);

            $importNodes = $mergeDoc->getElementsByTagNameNS(
                'http://symfony.com/schema/dic/constraint-mapping',
                'class'
            );

            foreach ($importNodes as $importNode) {
                $importNode = $this->document->importNode($importNode, true);
                $this->document->documentElement->appendChild($importNode);
            }
        }

        return $this;
    }

    /**
     * Stores the current document to the file system.
     *
     * @param string $path Location of the file to be stored.
     *
     * @return void
     */
    public function saveDocument($path)
    {
        set_error_handler(array($this, 'handleXmlError'));
        $this->document->save($path);
        restore_error_handler();
    }

    /**
     * Loads the provides file into a DomDocument;
     *
     * @param string   $xml     XML string/text to be loaded
     * @param int|null $options Set of libxml constants.
     *
     * @return \DOMDocument
     *
     * @link http://php.net/manual/en/libxml.constants.php
     */
    private function initDomDocument($xml, $options = null)
    {
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;

        if (!empty($options)) {
            $options = $this->options . '|' . $options;
        } else {
            $options = $this->options;
        }

        set_error_handler(array($this, 'handleXmlError'));
        $doc->loadXml($xml, $options);
        restore_error_handler();

        return $doc;
    }

    /**
     * Handles any error while reading the xml into a DomDocument
     *
     * @param string $errno  Error code
     * @param string $errstr Error message
     *
     * @throws ManipulatorException
     * @return false
     */
    public function handleXmlError($errno, $errstr)
    {
        if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
            throw new ManipulatorException('Failed to load the provided xml string into a DomDocument');
        } elseif ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::save()") > 0)) {
            throw new ManipulatorException('Failed to save document to the filesystem.');
        } else {
            return false;
        }
    }
}
