<?php
/**
 * listener that deletes cached languages when they get modified
 */

namespace Graviton\I18nBundle\Listener;

use Graviton\I18nBundle\Document\Language;
use Graviton\I18nBundle\Translator\Translator;
use Graviton\RestBundle\Event\ModelEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class I18nLanguageCachingInvalidationListener
{

    /**
     * invalidate when this document gets modified
     */
    const CHECK_DOCUMENT = Language::class;

    /** @var Translator */
    private $translator;

    /**
     * @param Translator $translator translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * deletes all languages from the cache if Language was modified
     *
     * @param ModelEvent $event Mongo.odm event argument
     *
     * @return void
     */
    public function invalidate(ModelEvent $event)
    {
        if ($event->getCollectionClass() === self::CHECK_DOCUMENT) {
            $this->translator->removeCachedLanguages();
        }
    }
}
