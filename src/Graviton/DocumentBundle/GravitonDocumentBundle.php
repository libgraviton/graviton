<?php
/**
 * integrate the mongodb flavour of the doctrine2-odm with graviton
 */

namespace Graviton\DocumentBundle;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormDataMapCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\Utils\DocumentMap;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Graviton\BundleBundle\GravitonBundleInterface;
use Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use Doctrine\ODM\MongoDB\Types\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Graviton\DocumentBundle\DependencyInjection\Compiler\ExtRefMappingCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\ExtRefFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\TranslatableFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFieldNamesCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFormFieldsCompilerPass;

/**
 * GravitonDocumentBundle
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonDocumentBundle extends Bundle implements GravitonBundleInterface
{
    /**
     * initialize bundle
     */
    public function __construct()
    {
        Type::registerType('extref', 'Graviton\DocumentBundle\Types\ExtReference');
        Type::registerType('hash', 'Graviton\DocumentBundle\Types\HashType');
    }


    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle[]
     */
    public function getBundles()
    {
        return array(
            new DoctrineMongoDBBundle(),
            new StofDoctrineExtensionsBundle(),
            new DoctrineFixturesBundle(),
        );
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

        $documentMap = new DocumentMap(
            (new Finder())
                ->in(__DIR__.'/../..')
                ->path('Resources/config/doctrine')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in(__DIR__.'/../..')
                ->path('Resources/config/serializer')
                ->name('*.xml'),
            (new Finder())
                ->in(__DIR__.'/../..')
                ->path('Resources/config')
                ->name('validation.xml')
        );

        $container->addCompilerPass(new ExtRefMappingCompilerPass());
        $container->addCompilerPass(new ExtRefFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new TranslatableFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new DocumentFormFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new DocumentFormDataMapCompilerPass($documentMap));
        $container->addCompilerPass(new DocumentFieldNamesCompilerPass($documentMap));
    }
}
