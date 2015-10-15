<?php
/**
 * core infrastructure like logging and framework.
 */

namespace Graviton\CoreBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\CacheBundle\GravitonCacheBundle;
use Graviton\DocumentBundle\GravitonDocumentBundle;
use Graviton\ExceptionBundle\GravitonExceptionBundle;
use Graviton\GeneratorBundle\GravitonGeneratorBundle;
use Graviton\I18nBundle\GravitonI18nBundle;
use Graviton\LogBundle\GravitonLogBundle;
use Graviton\PersonBundle\GravitonPersonBundle;
use Graviton\RabbitMqBundle\GravitonRabbitMqBundle;
use Graviton\RestBundle\GravitonRestBundle;
use Graviton\SchemaBundle\GravitonSchemaBundle;
use Graviton\SecurityBundle\GravitonSecurityBundle;
use Graviton\SwaggerBundle\GravitonSwaggerBundle;
use Graviton\FileBundle\GravitonFileBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\CoreBundle\Compiler\VersionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * GravitonCoreBundle
 *
 * WARNING: Don't change me without changing Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
            new GravitonI18nBundle(),
            new GravitonGeneratorBundle(),
            new GravitonPersonBundle(),
            new GravitonCacheBundle(),
            new GravitonLogBundle(),
            new GravitonSecurityBundle(),
            new GravitonSwaggerBundle(),
            new GravitonFileBundle(),
            new GravitonRabbitMqBundle(),
        );
    }

    /**
     * load version compiler pass
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new VersionCompilerPass());
    }
}
