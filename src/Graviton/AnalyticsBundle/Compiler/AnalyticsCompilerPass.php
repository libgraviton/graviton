<?php
/** compiler pass for analytics services */

namespace Graviton\AnalyticsBundle\Compiler;

use Graviton\Graviton;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticsCompilerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
		$services = [];
		$finder = Finder::create()
			->files()
			->in(Graviton::getBundleScanDir())
			->path('/\/analytics\//i')
			->name('*.json')
			->notName('_*')
            ->notName('*.pipeline.*')
			->sortByName();

		foreach ($finder as $file) {
			$key = $file->getFilename();
			$data = json_decode($file->getContents(), true);
			if (json_last_error()) {
				throw new InvalidConfigurationException(
					sprintf('Analytics file: %s could not be loaded due to error: %s', $key, json_last_error_msg())
				);
			}
			$services[$data['route']] = $data;
		}

		$container->setParameter('analytics.services', $services);
    }
}
