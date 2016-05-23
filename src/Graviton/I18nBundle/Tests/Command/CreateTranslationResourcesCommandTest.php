<?php
/**
 * functional tests for creating translation resource files
 */

namespace Graviton\I18nBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Graviton\GeneratorBundle\Command\GenerateBundleCommand;

/**
 * functional tests for graviton:generate:bundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 *
 * @todo fix that updateKernel is not getting tested
 */
class CreateTranslationResourcesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test graviton:i118n:create:resources command
     *
     * @return void
     */
    public function testLoadCreateCommand()
    {
        $languageMock = $this->getMockBuilder('\Graviton\I18nBundle\Repository\LanguageRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $enMock = $this->getMock('\Graviton\I18nBundle\Document\Language');
        $enMock->expects($this->any())->method('getId')->willReturn('en');

        $deMock = $this->getMock('\Graviton\I18nBundle\Document\Language');
        $deMock->expects($this->any())->method('getId')->willReturn('de');

        $languageMock->expects($this->once())
            ->method('findAll')
            ->willReturn([$enMock, $deMock]);

        $translatableMock = $this->getMockBuilder('\Graviton\I18nBundle\Repository\TranslatableRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $builderMock = $this->getMockbuilder('\Doctrine\ODM\MongoDB\Query\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $builderMock
            ->expects($this->once())
            ->method('distinct')
            ->with('domain')
            ->willReturn($builderMock);

        $builderMock
            ->expects($this->once())
            ->method('select')
            ->with('domain')
            ->willReturn($builderMock);

        $queryMock = $this->getMockBuilder('\Doctrine\ODM\MongoDB\Query\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $builderMock
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($queryMock);

        $queryMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn($queryMock);
        $queryMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(['core', 'i18n']);

        $translatableMock
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($builderMock);

        $fsMock = $this->getMock('\Symfony\Component\Filesystem\Filesystem');

        $fsMock->expects($this->exactly(4))
            ->method('touch');

        $command = new CommandTester(
            new \Graviton\I18nBundle\Command\CreateTranslationResourcesCommand(
                $languageMock,
                $translatableMock,
                $fsMock
            )
        );
        $command->execute(array());

        $this->assertContains('Creating translation resource stubs', $command->getDisplay());
        $this->assertContains('Generated file core.en.odm', $command->getDisplay());
        $this->assertContains('Generated file core.de.odm', $command->getDisplay());
        $this->assertContains('Generated file i18n.en.odm', $command->getDisplay());
        $this->assertContains('Generated file i18n.de.odm', $command->getDisplay());
    }
}
