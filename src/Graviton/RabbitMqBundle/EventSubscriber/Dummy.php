<?php

/**
 * Dummy EventSubscriber
 */

namespace Graviton\RabbitMqBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;

/**
 * dummy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class Dummy implements EventSubscriber
{
    /**
     * @return array Defines the doctrine events to subscribe to.
     */
    public function getSubscribedEvents()
    {
        return array();
    }
}
