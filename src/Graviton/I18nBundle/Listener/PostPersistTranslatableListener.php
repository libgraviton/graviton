<?php
/**
 * trigger update of translation loader cache when translatables are updated
 */

namespace Graviton\I18nBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\I18nBundle\Document\Translatable;

/**
 * trigger update of translation loader cache when translatables are updated
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PostPersistTranslatableListener implements EventSubscriber
{
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
            $domain = $object->getDomain();
            $locale = $object->getLocale();

            $triggerFile = __DIR__.'/../Resources/translations/'.$domain.'.'.$locale.'.odm';
            $fp = fopen($triggerFile, 'w');
            fwrite($fp, time());
            fclose($fp);

            $fp = fopen($triggerFile, 'w');
            $fstat = fstat($fp);

            $cacheFile = __DIR__.'/../../../../app/cache/test/translations/catalogue.'.$locale.'.php';
            $fpcache = fopen($cacheFile, 'w');
            $fstatcache = fstat($fpcache);
            fclose($fpcache);

            echo PHP_EOL.'FSTAT '.$triggerFile.' - ctime = '.$fstat['ctime'].' / mtime = '.$fstat['mtime'].' / diff = '.($fstat['ctime']-$fstat['mtime']).PHP_EOL;
            echo PHP_EOL.'FSTATCACHE '.$cacheFile.' - ctime = '.$fstatcache['ctime'].' / mtime = '.$fstatcache['mtime'].' / diff = '.($fstatcache['ctime']-$fstatcache['mtime']).PHP_EOL;


        }
    }
}
