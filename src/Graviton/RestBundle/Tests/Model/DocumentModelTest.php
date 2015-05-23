<?php
/**
 * document model test suite
 */
namespace Graviton\RestBundle\Tests\Model;

use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentModelTest extends GravitonTestCase
{

    public function testManageRepository()
    {
        $repositoryDouble = $this->getInterfaceTestDouble('\Doctrine\Common\Persistence\ObjectRepository');

        $model = $this->getModelProxy();

        $this->assertEmpty($model->getRepository());
        $this->assertInstanceOf('\Graviton\RestBundle\Model\DocumentModel', $model->setRepository($repositoryDouble));
        $this->assertSame($repositoryDouble, $model->getRepository());
    }

    /**
     * @param $containerDouble
     *
     * @return object
     */
    protected function getModelProxy($containerDouble = null)
    {
        $model = $this->getProxyBuilder('\Graviton\RestBundle\Model\DocumentModel')
            ->disableOriginalConstructor()
            ->setProperties(array('container'))
            ->getProxy();
        $model->container = $containerDouble;

        return $model;
    }
}
