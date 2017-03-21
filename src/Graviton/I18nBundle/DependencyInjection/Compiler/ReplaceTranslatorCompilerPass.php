<?php
/**
 * overrides the default translator with ours..
 */

namespace Graviton\I18nBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReplaceTranslatorCompilerPass implements CompilerPassInterface
{

    /**
     * create mapping from services
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->findDefinition('translator.default');
        $def->setClass('Graviton\I18nBundle\Translator\Translator');
        $container->setDefinition('translator.default', $def);
    }
}
