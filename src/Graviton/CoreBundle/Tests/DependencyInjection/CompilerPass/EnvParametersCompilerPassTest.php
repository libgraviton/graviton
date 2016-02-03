<?php
/**
 * EnvParametersCompilerPassTest class file
 */

namespace Graviton\CoreBundle\Tests\DependencyInjection\CompilerPass;

use Graviton\CoreBundle\Compiler\EnvParametersCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EnvParametersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parameterSettingDataProvider
     *
     * @param string $envName    env name
     * @param string $envValue   env value
     * @param string $paramName  param name
     * @param mixed  $paramValue param value
     * @param string $exception  optional expected exception
     *
     * @return void
     */
    public function testParameterSetting($envName, $envValue, $paramName, $paramValue, $exception = null)
    {
        $_SERVER[$envName] = $envValue;

        $containerDouble = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
                                ->disableOriginalConstructor()
                                ->getMock();

        if (is_null($exception)) {
            $containerDouble
                ->expects($this->exactly(1))
                ->method('setParameter')
                ->with(
                    $paramName,
                    $paramValue
                );
        } else {
            $this->setExpectedException($exception);
        }

        $compilerPass = new EnvParametersCompilerPass();
        $compilerPass->process($containerDouble);

        unset($_SERVER[$envName]);
    }

    /**
     * data provider for param settings
     *
     * @return array data
     */
    public function parameterSettingDataProvider()
    {
        return [
            'simple' => [
                'SYMFONY__test__parameter',
                'test',
                'test.parameter',
                'test'
            ],
            'simple-underscore' => [
                'SYMFONY__test__parameter_underscore',
                'test_underscore',
                'test.parameter_underscore',
                'test_underscore'
            ],
            'json-arr' => [
                'SYMFONY__test__parameter_json1',
                '[{"test": "test"}, {"test": "test"}]',
                'test.parameter_json1',
                [['test' => 'test'], ['test' => 'test']]
            ],
            'json-object' => [
                'SYMFONY__test__parameter_json2',
                '{"test": "test"}',
                'test.parameter_json2',
                ['test' => 'test']
            ],
            'json-invalid' => [
                'SYMFONY__test__parameter_json3',
                '{"test": "test",,}',
                null,
                null,
                '\RuntimeException'
            ]
        ];
    }
}
