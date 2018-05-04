<?php
/**
 * validate RepositoryFactory
 */

namespace Graviton\SchemaBundle\Tests\Service;

use Graviton\SchemaBundle\Service\RepositoryFactory;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RepositoryFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testGetMethod()
    {
        $managerRegistryMock = $this
            ->getMockBuilder('Doctrine\Bundle\MongoDBBundle\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $managerRegistryMock
            ->expects($this->once())
            ->method('getRepository')
            ->with('BundleName:Document');

        $sut = new RepositoryFactory(
            $managerRegistryMock
        );

        $sut->get('BundleName:Document');
    }
}
