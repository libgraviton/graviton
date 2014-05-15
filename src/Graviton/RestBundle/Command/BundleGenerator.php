<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Graviton\RestBundle\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\Container;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator as ParentGenerator;

/**
 * Generates a bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class BundleGenerator extends ParentGenerator
{
    private $filesystem;
    private $container;
    private $connecton;
    private $entityName;
    private $namespace;
    private $entityDir;
    private $routingPrexif;
    private $arrAnswers;

    public function __construct(Filesystem $filesystem, $container, $arrAnswers)
    {
        $this->filesystem = $filesystem;
        $this->container = $container;
        $this->arrAnswers = $arrAnswers;
    }

    public function generate($namespace, $bundle, $dir, $format, $structure)
    {
    $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            if (!is_dir($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" exists but is a file.', realpath($dir)));
            }
            $files = scandir($dir);
            if ($files != array('.', '..')) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
            }
            if (!is_writable($dir)) {
                throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not writable.', realpath($dir)));
            }
        }

        $this->setSkeletonDirs($this->getSkeletonDirs());
        
        $basename = substr($bundle, 0, -6);
        $parameters = array(
            'namespace' => $namespace,
            'bundle'    => $bundle,
            'format'    => $format,
            'bundle_basename' => $basename,
            'extension_alias' => Container::underscore($basename),
        );
        
        $serviceId = $this->arrAnswers['connection'].'_'.$this->arrAnswers['entityName']; 
        $parameters['service_id'] = $serviceId;
        
        $pagerId = $serviceId.'_pager';
        $parameters['pager_id'] = $pagerId;
        
        $parserId = $serviceId.'_parser';
        $parameters['parser_id'] = $parserId;
        
        $modelId = $serviceId.'_model';
        $parameters['model_id'] = $modelId;
        
        $routerId = $serviceId.'_router';
        $parameters['router_id'] = $routerId;
        
        $entityPath = $this->arrAnswers['namespace'].'\\'.$this->arrAnswers['entityDir'].'\\'.$this->arrAnswers['entityName'];
        $parameters['entity_path'] = $entityPath;
        
        $parameters['connection_name'] = $this->connecton;
        
        $parameters['routing_prefix'] = $this->routingPrexif;

        $this->renderFile('bundle/Bundle.php.twig', $dir.'/'.$bundle.'.php', $parameters);
        $this->renderFile('bundle/Extension.php.twig', $dir.'/DependencyInjection/'.$basename.'Extension.php', $parameters);
        $this->renderFile('bundle/Configuration.php.twig', $dir.'/DependencyInjection/Configuration.php', $parameters);

        if ('xml' === $format || 'annotation' === $format) {
            $this->renderFile('bundle/services.xml.twig', $dir.'/Resources/config/services.xml', $parameters);
        } else {
            $this->renderFile('bundle/services.'.$format.'.twig', $dir.'/Resources/config/services.'.$format, $parameters);
        }

        if ('annotation' != $format) {
            $this->renderFile('bundle/routing.'.$format.'.twig', $dir.'/Resources/config/routing.'.$format, $parameters);
        }
    }
    
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
    	$skeletonDirs = array();
    
    	if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/SensioGeneratorBundle/skeleton')) {
    		$skeletonDirs[] = $dir;
    	}
    
    	if (is_dir($dir = $this->container->get('kernel')->getRootdir().'/Resources/SensioGeneratorBundle/skeleton')) {
    		$skeletonDirs[] = $dir;
    	}
    
    	$skeletonDirs[] = __DIR__.'/../Resources/skeleton';
    	$skeletonDirs[] = __DIR__.'/../Resources';
    
    	return $skeletonDirs;
    }
}
