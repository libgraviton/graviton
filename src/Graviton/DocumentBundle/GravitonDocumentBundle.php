<?php
/**
 * integrate the mongodb flavour of the doctrine2-odm with graviton
 */

namespace Graviton\DocumentBundle;

use Graviton\DocumentBundle\DependencyInjection\Compiler\DocumentMapCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\ReadOnlyFieldsCompilerPass;
use Graviton\DocumentBundle\DependencyInjection\Compiler\RecordOriginExceptionFieldsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
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
class GravitonDocumentBundle extends Bundle
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
            new ExtRefMappingCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new ExtRefFieldsCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new RqlFieldsCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new TranslatableFieldsCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new DocumentFieldNamesCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new ReadOnlyFieldsCompilerPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION,
            5
        );
        $container->addCompilerPass(
            new RecordOriginExceptionFieldsCompilerPass(),
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
        $extRefConverter = $this->container->get('graviton.document.service.extrefconverter');
        $customType = Type::getType('hash');
        $customType->setExtRefConverter($extRefConverter);
    }
}
