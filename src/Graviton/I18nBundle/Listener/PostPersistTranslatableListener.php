<?php

namespace Graviton\I18nBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\I18nBundle\Document\Translatable;

/**
 * trigger update of translation loader cache when translatables are updated
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
            file_put_contents(
                __DIR__.'/../Resources/translations/'.$domain.'.'.$locale.'.odm',
                time()
            );
        }
    }
}
