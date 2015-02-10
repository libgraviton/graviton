<?php

namespace Graviton\GeneratorBundle\Tests\Generator;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;

/**
 * ResourceGeneratorProxy
 * Needed to make the generateDocument method public
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class ResourceGeneratorProxy extends ResourceGenerator
{
    /**
     * (non-PHPdoc)
     * @see \Graviton\GeneratorBundle\Generator\ResourceGenerator::generateDocument()
     */
    public function generateDocument($parameters, $dir, $document, $withRepository)
    {
        return parent::generateDocument($parameters, $dir, $document, $withRepository);
    }
}
