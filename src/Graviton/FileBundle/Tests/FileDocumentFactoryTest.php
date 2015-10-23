<?php
/**
 * Test suite to verify the file document factory
 */

namespace Graviton\FileBundle\Tests;

use Graviton\FileBundle\FileDocumentFactory;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileDocumentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testCreateFileMetaData()
    {
        $factory = new FileDocumentFactory();

        $this->assertInstanceOf(
            '\GravitonDyn\FileBundle\Document\FileMetaDataEmbedded',
            $factory->createFileMataData()
        );
    }

    /**
     * @return void
     */
    public function testInitiateFileMetaData()
    {
        $factory = new FileDocumentFactory();
        /** @var \GravitonDyn\FileBundle\Document\FileMetadata $fmd */
        $fmd = $factory->initiateFileMataData('foo', 15, 'test.txt', 'text/plain', []);

        $this->assertInstanceOf('\DateTime', $fmd->getCreatedate());
        $this->assertInstanceOf('\DateTime', $fmd->getModificationdate());
    }

    /**
     * @return void
     */
    public function testCreateFileLink()
    {
        $factory = new FileDocumentFactory();

        $this->assertInstanceOf(
            '\GravitonDyn\FileBundle\Document\FileLinksEmbedded',
            $factory->createFileLink()
        );
    }

    /**
     * @return void
     */
    public function testInitiateFileLinks()
    {
        $factory = new FileDocumentFactory();
        $fl = $factory->initializeFileLinks('owner', 'http://localhost/testcase/readonly/101');

        $this->assertEquals(
            'http://localhost/testcase/readonly/101',
            $fl->getRef()
        );
    }

    /**
     * @return void
     */
    public function testCreateFileMetadataAction()
    {
        $factory = new FileDocumentFactory();

        $this->assertInstanceOf(
            '\GravitonDyn\FileBundle\Document\FileMetadataActionEmbedded',
            $factory->createFileMetadataAction()
        );
    }
}
