<?php
/**
 * Validates the xml manipulator
 */

namespace Graviton\GeneratorBundle\Manipulator\File;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class XmlManipulatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testAddNodes()
    {
        $manip = new XmlManipulator();
        $manip->addNodes('<tag>foo</tag>');

        $this->assertAttributeEquals(array('<tag>foo</tag>'), 'nodes', $manip);
    }

    /**
     * @return void
     */
    public function testRenderDocument()
    {
        $xml = file_get_contents(__DIR__ . '/../../Resources/validation.xml');

        $manip = new XmlManipulator();
        $manip->addNodes($xml);

        $this->assertInstanceOf(
            '\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator',
            $manip->renderDocument($xml)
        );

        /** @var \DomDocument $document */
        $document = $this->readAttribute($manip, 'document');

        /** @var \DomElement $element */
        $element = $document->getElementsByTagName('class')->item(1);

        $this->assertEquals('GravitonDyn\FileBundle\Document\FileLinks', $element->getAttribute('name'));
    }

    /**
     * @return void
     */
    public function testSaveDocument()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM failed with segfault when executing DOMDocument::save() with empty filename');
        }

        $path = __DIR__ . '/../../Resources/validation.xml';
        $xml = file_get_contents($path);

        $manip = new XmlManipulator();
        $manip->addNodes($xml);
        $manip->renderDocument($xml);

        $this->expectException('\Graviton\GeneratorBundle\Manipulator\ManipulatorException');
        $manip->saveDocument("");
    }

    /**
     * @return void
     */
    public function testReset()
    {
        $manip = new XmlManipulator();
        $manip->addNodes('<tag>foo</tag>');

        $this->assertAttributeEquals(array('<tag>foo</tag>'), 'nodes', $manip);
        $this->assertInstanceOf(
            '\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator',
            $manip->reset()
        );
        $this->assertAttributeEmpty('nodes', $manip);
    }
}
