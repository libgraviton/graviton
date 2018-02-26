<?php
/**
 * event object for the translatable.persist event
 */

namespace Graviton\I18nBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class TranslatablePersistEvent extends Event
{

    /**
     * our event name
     *
     * @var string
     */
    const EVENT_NAME = 'translatable.persist';

    /**
     * locale
     *
     * @var string
     */
    private $locale;

    /**
     * domain
     *
     * @var string
     */
    private $domain;

    /**
     * constructor
     *
     * @param string $locale locale
     * @param string $domain domain
     */
    public function __construct($locale, $domain)
    {
        $this->locale = $locale;
        $this->domain = $domain;
    }

    /**
     * get locale
     *
     * @return string locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * get domain
     *
     * @return string domain
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
