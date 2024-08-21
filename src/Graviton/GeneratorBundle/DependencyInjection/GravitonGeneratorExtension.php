<?php
/**
 * GeneratorBundle DI config
 */

namespace Graviton\GeneratorBundle\DependencyInjection;

use Graviton\CoreBundle\DependencyInjection\GravitonBaseExtension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GravitonGeneratorExtension extends GravitonBaseExtension
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
