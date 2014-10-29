<?php

namespace Graviton\GeneratorBundle\Generator;

use Symfony\Component\DependencyInjection\Container;

/**
 * bundle containing various code generators
 *
 * This code is more or less loosley based on SensioBundleGenerator. It could
 * use some refactoring to duplicate less for that, but this is how i finally
 * got a working version.
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BundleGenerator extends AbstractGenerator
{
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
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" exists but is a file.',
                        realpath($dir)
                    )
                );
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" is not empty.',
                        realpath($dir)
                    )
                );
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to generate the bundle as the target directory "%s" is not writable.',
                        realpath($dir)
                    )
                );
            }
        }

        $author = trim(`git config --get user.name`);
        $email = trim(`git config --get user.email`);

        $basename = substr($bundle, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'format'    => $format,
            'author'    => $author,
            'email'     => $email,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );
        
        // if the name somehow includes "BundeBundle", we use a different template ;-)
        // this is used in the "graviton dynamic bundles", to generate the bundlebundle there.. 
        $bundleTemplate = 'bundle/Bundle.php.twig';
        if (strpos($bundle, 'BundleBundle') !== false) $bundleTemplate = 'bundle/BundleBundle.php.twig';

        $this->renderFile($bundleTemplate, $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile(
            'bundle/Extension.php.twig',
            $dir.'/DependencyInjection/'.$basename.'Extension.php',
            $parameters
        );

        if ('xml' === $format || 'annotation' === $format) {
            // @todo make this leave doctrine alone and move doctrine to a Manipulator in generate:resource
            $this->renderFile('bundle/services.xml.twig', $dir.'/Resources/config/services.xml', $parameters);
            mkdir($dir.'/Resources/config/doctrine');
            $this->renderFile('bundle/config.xml.twig', $dir.'/Resources/config/config.xml', $parameters);
        } else {
            $this->renderFile(
                'bundle/services.'.$format.'.twig',
                $dir.'/Resources/config/services.'.$format,
                $parameters
            );
            mkdir($dir.'/Resources/config/doctrine');
            $this->renderFile(
                'bundle/config.'.$format.'.twig',
                $dir.'/Resources/config/config.'.$format,
                $parameters
            );
        }

        if ('annotation' != $format) {
            $this->renderFile(
                'bundle/routing.'.$format.'.twig',
                $dir.'/Resources/config/routing.'.$format,
                $parameters
            );
        }
    }
}
