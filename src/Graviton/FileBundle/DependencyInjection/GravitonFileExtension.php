<?php
/**
 * load services into di
 */

namespace Graviton\FileBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonFileExtension extends GravitonBundleExtension
{
    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getConfigDir()
    {
        return __DIR__.'/../Resources/config';
    }
}
