<?php
/**
 * load services into di
 */

namespace Graviton\FileBundle\DependencyInjection;

use Graviton\CoreBundle\DependencyInjection\GravitonBaseExtension;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonFileExtension extends GravitonBaseExtension
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
