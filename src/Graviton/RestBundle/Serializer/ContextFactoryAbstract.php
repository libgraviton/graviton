<?php
/**
 * abstract for context factories
 */

namespace Graviton\RestBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class ContextFactoryAbstract
{

    /**
     * @var RequestStack
     */
    private $requestStack = null;

    /**
     * @var bool
     */
    private $setSerializeNull = true;

    /**
     * @var null|array
     */
    private $groups = null;

    /**
     * @var string
     */
    private $overrideHeaderName = null;

    /**
     * @var bool
     */
    private $overrideHeaderAllowed = false;

    /**
     * set RequestStack
     *
     * @param RequestStack $requestStack requestStack
     *
     * @return void
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * set SetSerializeNull
     *
     * @param bool $setSerializeNull setSerializeNull
     *
     * @return void
     */
    public function setSetSerializeNull($setSerializeNull)
    {
        $this->setSerializeNull = $setSerializeNull;
    }

    /**
     * set Groups
     *
     * @param array $groups groups
     *
     * @return void
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * set OverrideHeaderName
     *
     * @param null $overrideHeaderName overrideHeaderName
     *
     * @return void
     */
    public function setOverrideHeaderName($overrideHeaderName)
    {
        $this->overrideHeaderName = $overrideHeaderName;
    }

    /**
     * set OverrideHeaderAllowed
     *
     * @param bool $overrideHeaderAllowed overrideHeaderAllowed
     *
     * @return void
     */
    public function setOverrideHeaderAllowed($overrideHeaderAllowed)
    {
        $this->overrideHeaderAllowed = $overrideHeaderAllowed;
    }

    /**
     * sets the necessary properties on the context
     *
     * @param Context $context context
     *
     * @return Context context
     */
    protected function workOnInstance(Context $context)
    {
        $context = $context->setSerializeNull($this->setSerializeNull);

        // group override?
        if (true === $this->overrideHeaderAllowed && $this->requestStack instanceof RequestStack) {
            $headerValue = $this->requestStack->getCurrentRequest()->headers->get($this->overrideHeaderName);
            if (!is_null($headerValue)) {
                $this->groups = array_map('trim', explode(',', $headerValue));
            }
        }

        $serializerGroups = [GroupsExclusionStrategy::DEFAULT_GROUP];
        if (is_array($this->groups) && !empty($this->groups)) {
            $serializerGroups = array_merge($serializerGroups, $this->groups);
        }

        return $context->setGroups($serializerGroups);
    }
}
