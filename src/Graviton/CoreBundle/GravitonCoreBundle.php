<?php
/**
 * core infrastructure like logging and framework.
 */

namespace Graviton\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Graviton\ExceptionBundle\GravitonExceptionBundle;
use Graviton\DocumentBundle\GravitonDocumentBundle;
use Graviton\SchemaBundle\GravitonSchemaBundle;
use Graviton\RestBundle\GravitonRestBundle;
use Graviton\TestBundle\GravitonTestBundle;
use Graviton\TaxonomyBundle\GravitonTaxonomyBundle;
use Graviton\I18nBundle\GravitonI18nBundle;
use Graviton\PersonBundle\GravitonPersonBundle;

/**
 * GravitonCoreBundle
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new SensioFrameworkExtraBundle(),
            new GravitonExceptionBundle(),
            new GravitonDocumentBundle(),
            new GravitonSchemaBundle(),
            new GravitonRestBundle(),
            new GravitonTestBundle(),
            new GravitonTaxonomyBundle(),
            new GravitonI18nBundle(),
            new GravitonPersonBundle(),
        );
    }
}
