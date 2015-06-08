<?php
/**
 * check if form builder map is being generated correctly
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormMapCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DocumentFormMapCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processData
     *
     * @param string $id  service id
     * @param string $key expected key
     *
     * @return void
     */
    public function testProcess($id, $key)
    {
        $containerDouble = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $serviceDouble = $this->getMock('Symfony\Component\DependencyInjection\Definition');

        $containerDouble
            ->method('findTaggedServiceIds')
            ->willReturn($this->taggedServicesData());

        $containerDouble
            ->method('getDefinition')
            ->willReturn($serviceDouble);

        $serviceDouble
            ->method('getClass')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($this->getClassData()));

        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.form.type.document.service_map'),
                $this->contains($key)
            );

        $sut = new DocumentFormMapCompilerPass;
        $sut->process($containerDouble);
    }

    /**
     * @return array
     */
    public function processData()
    {
        return [
            'controller service to document' => [
                'graviton.core.controller.app',
                'Graviton\CoreBundle\Document\App'
            ],
            'service to document' => [
                'graviton.core.document.app',
                'Graviton\CoreBundle\Document\App'
            ],
            'controller class to document' => [
                'Graviton\CoreBundle\Controller\App',
                'Graviton\CoreBundle\Document\App'
            ],
            'class to document' => [
                'Graviton\CoreBundle\Document\App',
                'Graviton\CoreBundle\Document\App'
            ],
        ];
    }

    /**
     * @return array
     */
    public function taggedServicesData()
    {
        return [
            'graviton.core.controller.app' => [
                [],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getClassData()
    {
        return [
            'Graviton\CoreBundle\Controller\AppController'
        ];
    }
}
