<?php
/**
 * eventlistener that reacts on translatable persisting and manages the i18ncache
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18nBundle\Event\TranslatablePersistEvent;
use Graviton\I18nBundle\Service\I18nCacheUtils;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nCachingListener
{

    /**
     * cache utils
     *
     * @var I18nCacheUtils
     */
    private $cacheUtils;

    /**
     * constructor
     *
     * @param I18nCacheUtils $cacheUtils cache utils
     */
    public function __construct(I18nCacheUtils $cacheUtils)
    {
        $this->cacheUtils = $cacheUtils;
    }

    /**
     * hooked to the 'translatable.persist' event - invalidates the given locale/domain pair
     *
     * @param TranslatablePersistEvent $event event
     *
     * @return void
     */
    public function onPersist(TranslatablePersistEvent $event)
    {
        $this->cacheUtils->invalidate($event->getLocale(), $event->getDomain());
    }

    /**
     * hooked to the 'kernel.terminate' event - persists changes in the cache utils
     *
     * @param Event $event event
     *
     * @return void
     */
    public function onTerminate(Event $event)
    {
        $this->cacheUtils->processPending();
    }
}
