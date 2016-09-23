<?php
/**
 * proxy class for testing resourcegenerator
 */

namespace Graviton\GeneratorBundle\Tests\Generator;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * ResourceGeneratorProxy
 *
 * Needed to make the generateDocument method public
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class ResourceGeneratorProxy extends ResourceGenerator
{
    /**
     * (non-PHPdoc)
     * @see \Graviton\GeneratorBundle\Generator\ResourceGenerator::generateDocument()
     *
     * @param array  $parameters twig parameters
     * @param string $dir        base bundle dir
     * @param string $document   document name
     *
     * @return void
     */
    public function generateDocument($parameters, $dir, $document)
    {
        parent::generateDocument($parameters, $dir, $document);
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\GeneratorBundle\Generator\ResourceGenerator::generateDocument()
     *
     * @param string $dir base bundle dir
     *
     * @return void
     */
    public function generateParameters($dir)
    {
        parent::generateParameters($dir);
    }
}
