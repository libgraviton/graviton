<?php
/**
 * core infrastructure like logging and framework.
 */

namespace Graviton\CoreBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\CacheBundle\GravitonCacheBundle;
use Graviton\ConsultationBundle\GravitonConsultationBundle;
use Graviton\DocumentBundle\GravitonDocumentBundle;
use Graviton\EntityBundle\GravitonEntityBundle;
use Graviton\ExceptionBundle\GravitonExceptionBundle;
use Graviton\GeneratorBundle\GravitonGeneratorBundle;
use Graviton\I18nBundle\GravitonI18nBundle;
use Graviton\LogBundle\GravitonLogBundle;
use Graviton\PersonBundle\GravitonPersonBundle;
use Graviton\RestBundle\GravitonRestBundle;
use Graviton\SchemaBundle\GravitonSchemaBundle;
use Graviton\SecurityBundle\GravitonSecurityBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * GravitonCoreBundle
 *
 * WARNING: Don't change me without changing Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 *
 * @see \Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator
 */
class GravitonCoreBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * {@inheritDoc}
     *
     * set up graviton symfony bundles
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            new GravitonExceptionBundle(),
            new GravitonDocumentBundle(),
            new GravitonSchemaBundle(),
            new GravitonRestBundle(),
            new GravitonEntityBundle(),
            new GravitonI18nBundle(),
            new GravitonGeneratorBundle(),
            new GravitonPersonBundle(),
            new GravitonCacheBundle(),
            new GravitonLogBundle(),
            new GravitonConsultationBundle(),
            new GravitonSecurityBundle(),
        );
    }
}
