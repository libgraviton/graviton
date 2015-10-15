<?php
/**
 * a small child of the symfony Translator in order to load more dynamically
 */

namespace Graviton\I18nBundle\Translator;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Translator extends BaseTranslator {

    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
    {
        $cacheUtils = $container->get('graviton.18n.cacheutils');
        $options['resource_files'] = $cacheUtils->getResources($options['resource_files']);

        parent::__construct($container, $selector, $loaderIds, $options);
    }

}
