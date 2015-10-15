<?php
/**
 * trigger update of translation loader cache when translatables are updated
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Service\I18nCacheUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * trigger update of translation loader cache when translatables are updated
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PostPersistTranslatableListener implements EventSubscriber
{

    private $cacheUtils;

    public function __construct(I18nCacheUtils $cacheUtils)
    {
        $this->cacheUtils = $cacheUtils;
    }

    /**
     * {@inheritDocs}
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return array('postPersist');
    }

    /**
     * post persist callback method
     *
     * @param LifecycleEventArgs $event event args
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $object = $event->getObject();
        if ($object instanceof Translatable) {

            /*
            $domain = $object->getDomain();
            $locale = $object->getLocale();
            */

            $this->cacheUtils->invalidate($object->getLocale(), $object->getDomain());
        }
            /**
            $triggerFile = __DIR__.'/../Resources/translations/'.$domain.'.'.$locale.'.odm';
            $cacheDirMask = __DIR__.'/../../../../app/cache/translations';

            $fs = new Filesystem();
            if (!$fs->exists($triggerFile)) {
                $fs->touch($triggerFile);
            }

            try {
                $finder = new Finder();
                $finder
                    ->files()
                    ->in($cacheDirMask)
                    ->name('*.' . $locale . '.*');

                foreach ($finder as $file) {
                    $fs->remove($file->getRealPath());
                }
            } catch (\InvalidArgumentException $e) {
            }
            **/
        //}
    }
}
