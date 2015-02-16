<?php
/**
 * manage and load bundle config.
 */

namespace Graviton\SecurityBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonSecurityExtension extends GravitonBundleExtension
{
    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getConfigDir()
    {
        return __DIR__ . '/../Resources/config';
    }

    /**
     * {@inheritDoc}
     *
     * @param array            $configs   configs to load
     * @param ContainerBuilder $container builder used to load
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        // define alias for the strategy to extract the authentication key from the Airlock request.
        $container->setAlias(
            'graviton.security.authentication.strategy',
            $container->getParameter('graviton.security.authentication.strategy')
        );
    }
}
