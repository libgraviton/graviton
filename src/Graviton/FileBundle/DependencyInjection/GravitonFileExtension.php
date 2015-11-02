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
        $services = getenv('VCAP_SERVICES');
        if (!empty($services)) {
            $services = json_decode($services, true);
            $creds = $services['dynstrg'][0]['credentials'];

            $container->setParameter('graviton.file.gaufrette.backend', 's3');
            $container->setParameter('graviton.aws_s3.client.endpoint', sprintf('https://%s', $creds['accessHost']));
            $container->setParameter('graviton.aws_s3.client.key', $creds['accessKey']);
            $container->setParameter('graviton.aws_s3.client.secret', $creds['sharedSecret']);
            $container->setParameter('graviton.aws_s3.bucket_name', $services['dynstrg'][0]['name']);
        } else {
            $container->setParameter(
                'graviton.file.gaufrette.backend',
                $container->getParameter('graviton.file.backend')
            );
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
            $container->setParameter(
                'graviton.aws_s3.bucket_name',
                $container->getParameter('graviton.file.s3.bucket_name')
            );
        }
    }
}
