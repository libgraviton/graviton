<?php
/**
 * check if form builder map is being generated correctly
 */

namespace Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormMapCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;

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
        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/Resources/doctrine/form')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/Resources/serializer/form')
                ->name('*.xml')
        );
        $mappingClasses = [
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\A',
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\B',
            'Graviton\DocumentBundle\Tests\DependencyInjection\CompilerPass\Resources\Document\C',
        ];

        $serviceDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();
        $serviceDouble
            ->method('getClass')
            ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($this->getClassData()));

        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn($this->taggedServicesData());
        $containerDouble
            ->method('getDefinition')
            ->willReturn($serviceDouble);
        $containerDouble
            ->expects($this->once())
            ->method('setParameter')
            ->with(
                $this->equalTo('graviton.document.form.type.document.service_map'),
                $this->logicalAnd(
                    $this->arrayHasKey($id),
                    $this->contains($key),
                    new \PHPUnit_Framework_Constraint_ArraySubset(
                        array_combine($mappingClasses, $mappingClasses)
                    )
                )
            );

        $sut = new DocumentFormMapCompilerPass($documentMap);
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
            'controller class to document' => [
                'Graviton\CoreBundle\Controller\AppController',
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
     * @return string[]
     */
    public function getClassData()
    {
        return [
            'Graviton\CoreBundle\Controller\AppController'
        ];
    }
}
