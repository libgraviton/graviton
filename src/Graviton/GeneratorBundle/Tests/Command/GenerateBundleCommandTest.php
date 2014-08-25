<?php

namespace Graviton\GeneratorBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Graviton\GeneratorBundle\Command\GenerateBundleCommand;

/**
 * functional tests for graviton:generate:bundle
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 *
 * @todo refactor these to use something like CommandTester
 * @todo fix that these do no write coverage info atm
 */
class GenerateBundleCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test generating GravitonFooBundle
     *
     * @return void
     */
    public function testGenerateGravitonFooBundle()
    {
        $args = '--namespace=Graviton/FooBundle --dir=src --bundle-name=GravitonFooBundle --no-interaction';
        exec('php app/console graviton:generate:bundle '.$args.' 2>&1', $display);

        $this->assertContains('Generating the bundle code: OK', $display);
        $this->assertContains('Checking that the bundle is autoloaded: OK', $display);
        $this->assertContains('Enabling the bundle inside the core bundle: OK', $display);
        $this->markTestIncomplete();
    }

    /**
     * test for required params
     *
     * @return void
     */
    public function testRequiredParams()
    {
        exec('php app/console graviton:generate:bundle -n 2>&1', $display);
        $this->assertContains('  The "namespace" option must be provided.', $display);
        $this->markTestIncomplete();
    }
}
