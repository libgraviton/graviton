<?php
/**
 * functional tests for graviton:generate:bundle
 */

namespace Graviton\GeneratorBundle\Tests\Command;

use Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateBundleCommandTest as BaseTest;
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
class GenerateBundleCommandTest extends BaseTest
{
    /**
     * test basic calls to command
     *
     * @param array  $options  options
     * @param string $input    cli input
     * @param array  $expected results to assert
     *
     * @return void
     *
     * @dataProvider getInteractiveCommandData
     */
    public function testInteractiveCommand($options, $input, $expected)
    {
        parent::testInteractiveCommand($options, $input, $expected);
    }

    /**
     * add xml test to upstreams test data
     *
     * @return array[]
     *
     * @codeCoverageIgnore
     */
    public function getInteractiveCommandData()
    {
        $tmp = sys_get_temp_dir();

        return array_merge(
            parent::getInteractiveCommandData(),
            array(
                array(
                    array(
                        '--dir' => $tmp,
                        '--format' => 'xml'
                    ),
                    "Foo/BarBundle\n",
                    array('Foo\BarBundle', 'FooBarBundle', $tmp.'/', 'xml', false)
                )
            )
        );
    }

    /**
     * get command
     *
     * @param \Graviton\GeneratorBundle\Generator\BundleGenerator $generator generator
     * @param object                                              $input     input mock
     *
     * @return \Graviton\GeneratorBundle\Command\GenerateBundleCommand
     */
    protected function getCommand($generator, $input)
    {
        $command = $this
            ->getMockBuilder('Graviton\GeneratorBundle\Command\GenerateBundleCommand')
            ->setMethods(array('checkAutoloader', 'updateKernel'))
            ->getMock();

        $command->setContainer($this->getContainer());
        $command->setHelperSet($this->getHelperSet($input));
        $command->setGenerator($generator);

        return $command;
    }

    /**
     * get generator
     *
     * @return \Graviton\GeneratorBundle\Generator\BundleGenerator
     */
    protected function getGenerator()
    {
        // get a noop generator
        return $this
            ->getMockBuilder('Graviton\GeneratorBundle\Generator\BundleGenerator')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
    }

    /**
     * get bundle
     *
     * @return \Graviton\BundleBundle\GravitonBundleInterface
     *
     * @todo move one class up
     */
    protected function getBundle()
    {
        $bundle = $this->getMock('Graviton\BundleBundle\GravitonBundleBundle');
        $bundle
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(sys_get_temp_dir()));

        return $bundle;
    }
}
