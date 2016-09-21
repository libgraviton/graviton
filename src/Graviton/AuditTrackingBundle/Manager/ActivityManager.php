<?php
/**
 * Keeping all activity in one place to be controlled
 */

namespace Graviton\AuditTrackingBundle\Manager;

use Graviton\AuditTrackingBundle\Document\AuditTracking;
use Graviton\RestBundle\Event\ModelEvent;
use Guzzle\Http\Message\Header;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActivityManager
 * @package Graviton\AuditTrackingBundle\Manager
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ActivityManager
{
    /** Max char length of saved content data */
    const CONTENT_MAX_LENGTH = 2048;

    /** @var bool If log is enabled */
    private $enabled = false;

    /** @var Request $request */
    private $request;

    /** @var array */
    private $configurations;

    /** @var AuditTracking */
    private $document;

    /** @var array Events that shall be stored */
    private $events = [];

    /** @var string  */
    private $globalRequestLocation = '';

    /**
     * DBActivityListener constructor.
     *
     * @param RequestStack  $requestStack Sf request data
     * @param AuditTracking $document     DocumentCollection for event
     */
    public function __construct(
        RequestStack  $requestStack,
        AuditTracking $document
    ) {
        $this->request = $requestStack ? $requestStack->getCurrentRequest() : false;
        $this->document = $document;
    }

    /**
     * Set permission and access configuration
     *
     * @param array $configurations key value config
     * @return void
     */
    public function setConfiguration(array $configurations)
    {
        $this->configurations = $configurations;
        if ($this->runTracking()) {
            $this->enabled = true;
        }
    }

    /**
     * Return casted value from configuration.
     *
     * @param string $key  Configuration key
     * @param string $cast Type of object is expected to be returned
     * @return int|string|bool|array
     * @throws ParameterNotFoundException
     */
    public function getConfigValue($key, $cast = 'string')
    {
        if (array_key_exists($key, $this->configurations)) {
            if ('bool' == $cast) {
                return (boolean) $this->configurations[$key];
            }if ('array' == $cast) {
                return (array) $this->configurations[$key];
            } elseif ('string' == $cast) {
                return (string) $this->configurations[$key];
            } elseif ('int' == $cast) {
                return (int) $this->configurations[$key];
            }
        }
        throw new ParameterNotFoundException('ActivityManager could not find required configuration: '.$key);
    }

    /**
     * Check if this the Call has to be logged
     *
     * @return bool
     */
    private function runTracking()
    {
        //Ignore if no request, import fixtures.
        if (!$this->request) {
            return false;
        }

        // Check if enable
        if (!$this->getConfigValue('log_enabled', 'bool')) {
            return false;
        }
        
        // We never log tracking service calls
        $excludeUrls = $this->getConfigValue('exlude_urls', 'array');
        if ($excludeUrls) {
            $currentUrl = $this->request->getRequestUri();
            foreach ($excludeUrls as $url) {
                if (substr($currentUrl, 0, strlen($url)) == $url) {
                    return false;
                }
            }
        }

        // Check if we wanna log test and localhost calls
        if (!$this->getConfigValue('log_test_calls', 'bool')
            && !in_array($this->request->getHost(), ['localhost', '127.0.0.1'])) {
            return false;
        }

        return true;
    }

    /**
     * Incoming request done by user
     * @param Request $request sf response priority 1
     * @return void
     */
    public function registerRequestEvent(Request $request)
    {
        if (!$this->enabled) {
            return;
        }
        // Check if this request event shall be registered
        $saveEvents = $this->getConfigValue('requests', 'array');
        $method = $request->getMethod();
        $this->globalRequestLocation = $request->getRequestUri();
        if (!in_array($method, $saveEvents)) {
            return;
        }

        $content = substr($request->getContent(), 0, self::CONTENT_MAX_LENGTH);

        $data = ['ip' => $request->getClientIp()];

        if ($this->getConfigValue('request_headers', 'bool')) {
            $data['headers'] = $request->headers->all();
        }
        if ($length=$this->getConfigValue('request_content', 'int')) {
            $cnt = mb_check_encoding($content, 'UTF-8') ? $content : 'Content omitted, since it is not utf-8';
            $data['content'] = ($length==1) ? $cnt : substr($cnt, 0, $length);
        }

        /** @var AuditTracking $event */
        $event = new $this->document();
        $event->setAction('request');
        $event->setType($method);
        $event->setData((object) $data);
        $event->setLocation($request->getRequestUri());
        $event->setCreatedAt(new \DateTime());
        $this->events[] = $event;
    }

    /**
     * The response returned to user
     *
     * @param Response $response sf response
     * @return void
     */
    public function registerResponseEvent(Response $response)
    {
        if (!$this->enabled) {
            return;
        }
        if (!$this->getConfigValue('response', 'bool')) {
            return;
        }

        $data = [];
        $statusCode = '0';

        if (method_exists($response, 'getStatusCode')) {
            $statusCode = $response->getStatusCode();
        }
        if ($length=$this->getConfigValue('response_content', 'int') && method_exists($response, 'getContent')) {
            $cnt = mb_check_encoding($response->getContent(), 'UTF-8') ?
                $response->getContent() : 'Content omitted, since it is not utf-8';
            $data['content'] = ($length==1) ? $cnt : substr($cnt, 0, $length);
        }
        if ($this->getConfigValue('response_content', 'bool')) {
            $data['header']  = $response->headers->all();
        }

        // Header links
        $location = $this->extractHeaderLink($response->headers->get('link'), 'self');

        /** @var AuditTracking $audit */
        $audit = new $this->document();
        $audit->setAction('response');
        $audit->setType($statusCode);
        $audit->setData((object) $data);
        $audit->setLocation($location);
        $audit->setCreatedAt(new \DateTime());
        $this->events[] = $audit;
    }

    /**
     * Capture possible un-handled exceptions in php
     *
     * @param \Exception $exception The exception thrown in service.
     * @return void
     */
    public function registerExceptionEvent(\Exception $exception)
    {
        if (!$this->enabled) {
            return;
        }
        if (!$this->getConfigValue('exceptions', 'bool')) {
            return;
        }
        $data = (object) [
            'message'   => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString()
        ];

        /** @var AuditTracking $audit */
        $audit = new $this->document();
        $audit->setAction('exception');
        $audit->setType($exception->getCode());
        $audit->setData($data);
        $audit->setLocation(get_class($exception));
        $audit->setCreatedAt(new \DateTime());
        $this->events[] = $audit;
    }

    /**
     * Any database events, update, save or delete
     *
     * Available $event->getCollection() would give you the full object.
     *
     * @param ModelEvent $event Document object changed
     * @return void
     */
    public function registerDocumentModelEvent(ModelEvent $event)
    {
        if (!$this->enabled) {
            return;
        }
        if ((!($dbEvents = $this->getConfigValue('database', 'array')))) {
            return;
        }
        if (!in_array($event->getAction(), $dbEvents)) {
            return;
        }

        $data = (object) [
            'class' => $event->getCollectionClass()
        ];

        /** @var AuditTracking $audit */
        $audit = new $this->document();
        $audit->setAction($event->getAction());
        $audit->setType('collection');
        $audit->setData($data);
        $audit->setLocation($this->globalRequestLocation);
        $audit->setCollectionId($event->getCollectionId());
        $audit->setCollectionName($event->getCollectionName());
        $audit->setCreatedAt(new \DateTime());

        $this->events[] = $audit;
    }

    /**
     * Parse and extract customer header links
     *
     * @param string $strHeaderLink sf header links
     * @param string $extract       desired key to be found
     * @return string
     */
    private function extractHeaderLink($strHeaderLink, $extract = 'self')
    {
        if (!$strHeaderLink) {
            return '';
        }

        $parts = [];
        foreach (explode(',', $strHeaderLink) as $link) {
            $link = explode(';', $link);
            if (count($link)==2) {
                $parts[str_replace(['rel=','"'], '', trim($link[1]))] =  str_replace(['<','>'], '', $link[0]);
            }
        }

        return  array_key_exists($extract, $parts) ? $parts[$extract] : '';
    }

    /**
     * Get events AuditTracking
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }
}
