<?php
/**
 * manage and load bundle config.
 */

namespace Graviton\DocumentBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://scm.to/004w}
 *
 * @category Graviton
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GravitonDocumentExtension extends GravitonBundleExtension
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
     * Overwrite mongodb config from parent in cloud case.
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        /** [nue]
         * this is a workaround for a current symfony bug:
         * https://github.com/symfony/symfony/issues/7555
         *
         * we *want* to be able to override any param with our env variables..
         * so we do again, what the kernel did already here.. ;-)
         */
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'SYMFONY__')) {
                $container->setParameter(strtolower(str_replace('__', '.', substr($key, 9))), $value);
            }
        }

        // populated from cf's VCAP_SERVICES variable
        $services = $container->getParameter('vcap.services');
        if (!empty($services)) {
            $services = json_decode($services, true);
            $mongo = $services['mongodb-2.2'][0]['credentials'];

            $container->setParameter('mongodb.default.server.uri', $mongo['url']);
            $container->setParameter('mongodb.default.server.db', $mongo['db']);
        }
    }
}
