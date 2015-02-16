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
 * @category GravitonSecutityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
