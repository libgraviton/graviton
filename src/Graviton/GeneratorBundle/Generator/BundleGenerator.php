<?php
/**
 * various code generators
 */

namespace Graviton\GeneratorBundle\Generator;

use Symfony\Component\DependencyInjection\Container;

/**
 * bundle containing various code generators
 *
 * This code is more or less loosley based on SensioBundleGenerator. It could
 * use some refactoring to duplicate less for that, but this is how i finally
 * got a working version.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BundleGenerator extends AbstractGenerator
{

    /**
     * @var bool
     */
    private $generateSerializerConfig = true;

    /**
     * set GenerateSerializerConfig
     *
     * @param bool $generateSerializerConfig generateSerializerConfig
     *
     * @return void
     */
    public function setGenerateSerializerConfig($generateSerializerConfig)
    {
        $this->generateSerializerConfig = $generateSerializerConfig;
    }

    /**
     * generate bundle code
     *
     * @param string $namespace namspace name
     * @param string $bundle    bundle name
     * @param string $dir       bundle dir
     * @param string $format    bundle condfig file format
     *
     * @return void
     */
    public function generate($namespace, $bundle, $dir, $format)
    {
        $dir .= '/' . strtr($namespace, '\\', '/');

        // make sure we have no trailing \ in namespace
        if (substr($namespace, -1) == '\\') {
            $namespace = substr($namespace, 0, -1);
        }

        $basename = $this->getBundleBaseName($bundle);
        $parameters = array(
            'namespace' => $namespace,
            'bundle' => $bundle,
            'format' => $format,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
            'generateSerializerConfig' => $this->generateSerializerConfig
        );

        $this->renderFile('bundle/Bundle.php.twig', $dir . '/' . $bundle . '.php', $parameters);
        $this->renderFile(
            'bundle/Extension.php.twig',
            $dir . '/DependencyInjection/' . $basename . 'Extension.php',
            $parameters
        );

        $this->renderFile('bundle/config.xml.twig', $dir . '/Resources/config/config.xml', $parameters);

        if ('xml' === $format || 'annotation' === $format) {
            $this->renderFile('bundle/services.xml.twig', $dir . '/Resources/config/services.xml', $parameters);
        } else {
            $this->renderFile(
                'bundle/services.' . $format . '.twig',
                $dir . '/Resources/config/services.' . $format,
                $parameters
            );
        }

        if ('annotation' != $format) {
            $this->renderFile(
                'bundle/routing.' . $format . '.twig',
                $dir . '/Resources/config/routing.' . $format,
                $parameters
            );
        }
    }
}
