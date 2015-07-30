<?php
/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\I18nBundle\Document\Translatable;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * tries to alter rql queries in a way the user can search translatables in all languages
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class I18nRqlParsingListener implements EventSubscriber
{
    /**
     * {@inheritDocs}
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('visitNode');
    }

    /**
     * @param VisitNodeEvent $event node event to visit
     *
     * @return VisitNodeEvent
     */
    public function onVisitNode(VisitNodeEvent $event)
    {
        $node = $event->getNode();
        return $event;
    }

}
