<?php
/**
 * SchemaFactoryTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Validator;

use Graviton\GeneratorBundle\Definition\Validator\SchemaFactory;

/**
 * Test SchemaFactory
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test SchemaFactory::createSchema()
     *
     * @return void
     */
    public function testCreateSchema()
    {
        $uri = __METHOD__;
        $schema = (object) [__METHOD__ => __LINE__];

        $retriever = $this->getMockBuilder('HadesArchitect\JsonSchemaBundle\Uri\UriRetrieverServiceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $retriever->expects($this->once())
            ->method('retrieve')
            ->with($uri)
            ->willReturn($schema);

        $resolver = $this->getMockBuilder('JsonSchema\RefResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->expects($this->once())
            ->method('getUriRetriever')
            ->willReturn($retriever);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($schema, $uri);

        $this->assertEquals(
            $schema,
            (new SchemaFactory($resolver))->createSchema($uri)
        );
    }
}
