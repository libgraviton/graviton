<?php
/**
 * load services into di
 */

namespace Graviton\FileBundle\DependencyInjection;

use Graviton\BundleBundle\DependencyInjection\GravitonBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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

    /**
     * Overwrite S3 config from cloud if available
     *
     * @param ContainerBuilder $container instance
     *
     * @return void
     */
    public function prepend(ContainerBuilder $container)
    {
        parent::prepend($container);

        // populated from cf's VCAP_SERVICES variable
        $services = $container->getParameter('vcap.services');
        if (!empty($services)) {
            $services = json_decode($services, true);
            $s3 = $services['atmoss3'][0]['credentials'];

            $container->setParameter('graviton.aws_s3.client.endpoint', $s3['accessHost']);
            $container->setParameter('graviton.aws_s3.client.key', $s3['accessKey']);
            $container->setParameter('graviton.aws_s3.client.secret', $s3['sharedSecret']);
        } else {
            $container->setParameter(
                'graviton.aws_s3.client.endpoint',
                $container->getParameter('graviton.file.s3.endpoint')
            );
            $container->setParameter(
                'graviton.aws_s3.client.key',
                $container->getParameter('graviton.file.s3.key')
            );
            $container->setParameter(
                'graviton.aws_s3.client.secret',
                $container->getParameter('graviton.file.s3.secret')
            );
        }
    }
}
