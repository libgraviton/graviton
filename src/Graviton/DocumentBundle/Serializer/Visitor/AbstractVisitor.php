<?php
/**
 * abstract visitor
 */

declare(strict_types=1);

namespace Graviton\DocumentBundle\Serializer\Visitor;

use JMS\Serializer\GraphNavigatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class AbstractVisitor
{
    /**
     * @var GraphNavigatorInterface
     */
    protected GraphNavigatorInterface $navigator;

    /**
     * @param GraphNavigatorInterface $navigator navigator
     *
     * @return void
     */
    public function setNavigator(GraphNavigatorInterface $navigator): void
    {
        $this->navigator = $navigator;
    }

    /**
     * @param mixed $data data
     * @return void
     */
    public function prepare($data)
    {
        return $data;
    }

    /**
     * gets element type
     *
     * @param array $typeArray type arr
     * @return array|null type
     */
    protected function getElementType(array $typeArray): ?array
    {
        if (false === isset($typeArray['params'][0])) {
            return null;
        }

        if (isset($typeArray['params'][1]) && \is_array($typeArray['params'][1])) {
            return $typeArray['params'][1];
        } else {
            return $typeArray['params'][0];
        }
    }
}
