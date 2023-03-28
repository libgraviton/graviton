<?php
/**
 * builds some params that are used by SecurityUtils in regard to restrictions
 */

namespace Graviton\SecurityBundle\DependencyInjection\Compiler;

use Graviton\CoreBundle\Util\CoreUtils;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionCompilerPass implements CompilerPassInterface
{
    /**
     * create mapping from services
     *
     * @param ContainerBuilder $container container builder
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $params = [
            'graviton.rest.data_restriction.map',
            'graviton.rest.data_restriction.conditional.persist.map'
        ];

        foreach ($params as $param) {
            $mapConfigured = $container->getParameter($param);
            $map = [];

            foreach ($mapConfigured as $headerName => $fieldName) {
                $fieldSpec = CoreUtils::parseStringFieldList($fieldName);
                if (count($fieldSpec) != 1) {
                    throw new \LogicException("Wrong data restriction value as '{$headerName}' '{$fieldName}'");
                }

                $map[$headerName] = array_pop($fieldSpec);
            }

            $container->setParameter($param.'.compiled', $map);
        }
    }
}
