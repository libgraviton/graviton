<?php
/**
 * a small child of the symfony Translator in order to load more dynamically.
 * as we cannot inject more properties into this via DIC, our stuff is in I18nCacheUtils.
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
class Translator extends BaseTranslator
{

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *   * resource_files: List of translation resources available grouped by locale.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     * @param MessageSelector    $selector  The message selector for pluralization
     * @param array              $loaderIds An array of loader Ids
     * @param array              $options   An array of options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ContainerInterface $container,
        MessageSelector $selector,
        $loaderIds = array(),
        array $options = array()
    ) {
        $cacheUtils = $container->get('graviton.18n.cacheutils');
        $options['resource_files'] = $cacheUtils->getResources($options['resource_files']);
        parent::__construct($container, $selector, $loaderIds, $options);
    }
}
