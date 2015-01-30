<?php

namespace Graviton\I18nBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Graviton\GeneratorBundle\Command\GenerateBundleCommand;

/**
 * functional tests for graviton:generate:bundle
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @link     http://swisscom.com
 *
 * @todo fix that updateKernel is not getting tested
 */
class LoadMissingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test graviton:i118n:load:missing command
     *
     * @return void
     */
    public function testLoadMissingCommand()
    {
        $command = new CommandTester($this->getCommand());
        $command->execute(array());
    }

    /**
     * get command
     *
     * @return \Graviton\GeneratorBundle\Command\GenerateBundleCommand
     */
    protected function getCommand()
    {
        $command = $this
            ->getMockBuilder('Graviton\I18nBundle\Command\LoadMissingCommand')
            ->setMethods(array('generateForLanguage'))
            ->getMock();

        $command->setContainer($this->getContainer());

        return $command;
    }

    /**
     * get mock container
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainer()
    {
        $englishLang = $this
            ->getMockBuilder('Graviton\I18nBundle\Document\Language')
            ->setMethods(array('getId'))
            ->getMock();
        $englishLang
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('en'));
        $germanLang = $this
            ->getMockBuilder('Graviton\I18nBundle\Document\Language')
            ->setMethods(array('getId'))
            ->getMock();
        $germanLang
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('de'));
        $languageRepo = $this
            ->getMockBuilder('Graviton\I18nBundle\Repository\Language')
            ->setMethods(array('findAll'))
            ->getMock();
        $languageRepo
            ->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue(array($englishLang, $germanLang)));
        $translatableDocument = $this
            ->getMockBuilder('Graviton\I18nBundle\Document\Translatable')
            ->setMethods(array('getDomain', 'getOriginal'))
            ->getMock();
        $translatable = $this
            ->getMockBuilder('Graviton\I18nBundle\Document\Translatable')
            ->setMethods(array('findBy'))
            ->getMock();
        $translatable
            ->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(array($translatableDocument)));
        $model = $this
            ->getMockBuilder('Graviton\I18nBundle\Model\Translatable')
            ->disableOriginalConstructor()
            ->setMethods(array('insertRecord'))
            ->getMock();
        $model
            ->expects($this->once())
            ->method('insertRecord');
        $container = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->setMethods(array('get'))
            ->getMock();
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->onConsecutiveCalls($languageRepo, $translatable, $model));

        return $container;
    }
}
