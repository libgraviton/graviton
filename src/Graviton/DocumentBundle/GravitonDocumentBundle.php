<?php
/**
 * integrate the mongodb flavour of the doctrine2-odm with graviton
 */

namespace Graviton\DocumentBundle;

use Graviton\DocumentBundle\DependencyInjection\Compiler\ReadOnlyFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\RecordOriginExceptionFieldsCompilerPass;
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
use Graviton\DocumentBundle\DependencyInjection\Compiler\RqlFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\TranslatableFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentFieldNamesCompilerPass;

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
        // TODO: implement ExtReferenceArrayType
        Type::registerType('extref', Types\ExtReferenceType::class);
        Type::registerType('hash', Types\HashType::class);
        Type::registerType('hasharray', Types\HashArrayType::class);
        Type::registerType('datearray', Types\DateArrayType::class);
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

        // If it's inside vendor library or running as graviton base.
        $rootDir = $container->getParameter('kernel.root_dir');
        if (strpos($rootDir, 'vendor/graviton')) {
            $dirs = [
                $rootDir.'/../..'
            ];
        } else {
            $dirs = [
                __DIR__ . '/../..',
                $rootDir.'/../vendor/graviton'
            ];
        }

        $documentMap = new DocumentMap(
            (new Finder())
                ->in($dirs)
                ->path('Resources/config/doctrine')
                ->name('*.mongodb.xml'),
            (new Finder())
                ->in($dirs)
                ->path('Resources/config/serializer')
                ->name('*.xml'),
            (new Finder())
                ->in($dirs)
                ->path('Resources/config')
                ->name('validation.xml'),
            (new Finder())
                ->in($dirs)
                ->path('Resources/config/schema')
                ->name('*.json')
        );

        $container->addCompilerPass(new ExtRefMappingCompilerPass());
        $container->addCompilerPass(new ExtRefFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new RqlFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new TranslatableFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new DocumentFieldNamesCompilerPass($documentMap));
        $container->addCompilerPass(new ReadOnlyFieldsCompilerPass($documentMap));
        $container->addCompilerPass(new RecordOriginExceptionFieldsCompilerPass($documentMap));
    }

    /**
     * boot bundle function
     *
     * @return void
     */
    public function boot()
    {
        $extRefConverter = $this->container->get('graviton.document.service.extrefconverter');
        $customType = Type::getType('hash');
        $customType->setExtRefConverter($extRefConverter);
    }
}
