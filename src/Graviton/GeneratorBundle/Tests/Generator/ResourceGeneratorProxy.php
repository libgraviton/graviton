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
 * @category GravitonRestBundle
 * @package  Graviton
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
     * @param array   $parameters     twig parameters
     * @param string  $dir            base bundle dir
     * @param string  $document       document name
     * @param boolean $withRepository generate repository class
     *
     * @return void
     */
    public function generateDocument($parameters, $dir, $document, $withRepository)
    {
        return parent::generateDocument($parameters, $dir, $document, $withRepository);
    }
}
