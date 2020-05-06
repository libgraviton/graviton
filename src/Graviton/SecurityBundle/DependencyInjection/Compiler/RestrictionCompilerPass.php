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
        $restrictionMapConfigured = $container->getParameter('graviton.rest.data_restriction.map');
        $restrictionMap = [];

        foreach ($restrictionMapConfigured as $headerName => $fieldName) {
            $fieldSpec = CoreUtils::parseStringFieldList($fieldName);
            if (count($fieldSpec) != 1) {
                throw new \LogicException("Wrong data restriction value as '${headerName}' '${fieldName}'");
            }

            $restrictionMap[$headerName] = array_pop($fieldSpec);
        }

        $container->setParameter('graviton.rest.data_restriction.map.compiled', $restrictionMap);
    }
}
