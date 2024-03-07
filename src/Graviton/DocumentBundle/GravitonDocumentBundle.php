<?php
/**
 * integrate the mongodb flavour of the doctrine2-odm with graviton
 */

namespace Graviton\DocumentBundle;

use Graviton\BundleBundle\GravitonBundleInterface;
use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentMapCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\SolrDefinitionCompilerPass;
use Graviton\DocumentBundle\Types\TypeLoader;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ODM\MongoDB\Types\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Graviton\DocumentBundle\DependencyInjection\Compiler\ExtRefFieldsCompilerPass;

/**
 * GravitonDocumentBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonDocumentBundle extends Bundle implements GravitonBundleInterface
{

    /**
     * initialize bundle
     */
    public function __construct()
    {
        TypeLoader::load();
    }


    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return [];
    }

    /**
     * load compiler pass
     *
     * @param ContainerBuilder $container container builder
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(
            new DocumentMapCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            100
        );
        $container->addCompilerPass(
            new ExtRefFieldsCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new SolrDefinitionCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
    }

    /**
     * boot bundle function
     *
     * @return void
     */
    public function boot()
    {
        $extRefConverter = $this->container->get('Graviton\DocumentBundle\Service\ExtReferenceConverter');
        $customType = Type::getType('hash');
        $customType->setExtRefConverter($extRefConverter);
    }
}
