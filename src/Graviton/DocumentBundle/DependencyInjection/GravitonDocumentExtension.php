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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
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
         * this is a workaround for a new symfony feature:
         * https://github.com/symfony/symfony/issues/7555
         *
         * we *need* to be able to override any param with our env variables..
         * so we do again, what the kernel did already here.. ;-)
         *
         * Since fabpot seems to have said bye to this feature we are
         * re-implementing it here. We are also adding some fancy json
         * parsing for hashes and arrays while at it.
         *
         * @todo move this out of file bundle as it is much more global
         * @todo add proper documentation on this "feature" to a README
         */
        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'SYMFONY__')) {

                if (substr($value, 0, 1) == '[' || substr($value, 0, 1) == '{') {
                    $value = json_decode($value, true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \RuntimeException(
                            sprintf('error "%s" in env variable "%s"', json_last_error_msg(), $key)
                        );
                    }
                }

                $container->setParameter(strtolower(str_replace('__', '.', substr($key, 9))), $value);
            }
        }

        // grab mongo config directly from vcap...
        $services = getenv('VCAP_SERVICES');
        if (!empty($services)) {
            $services = json_decode($services, true);
            $mongo = $services['mongodb'][0]['credentials'];

            $container->setParameter('mongodb.default.server.uri', $mongo['uri']);
            $container->setParameter('mongodb.default.server.db', $mongo['database']);
        } else {
            $container->setParameter(
                'mongodb.default.server.uri',
                $container->getParameter('graviton.mongodb.default.server.uri')
            );
            $container->setParameter(
                'mongodb.default.server.db',
                $container->getParameter('graviton.mongodb.default.server.db')
            );
        }
    }
}
