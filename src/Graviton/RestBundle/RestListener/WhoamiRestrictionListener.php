<?php
/**
 * listener that restricts data to current user
 */

namespace Graviton\RestBundle\RestListener;

use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Graviton\SecurityBundle\Service\SecurityUtils;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WhoamiRestrictionListener extends RestListenerAbstract
{

    /**
     * @var SecurityUtils
     */
    private $securityUtils;

    /**
     * @var string[]
     */
    private $fieldNames = [];

    /**
     * set SecurityUtils
     *
     * @param SecurityUtils $securityUtils securityUtils
     *
     * @return void
     */
    public function setSecurityUtils($securityUtils)
    {
        $this->securityUtils = $securityUtils;
    }

    /**
     * get FieldNames
     *
     * @return string[] FieldNames field names
     */
    public function getFieldNames()
    {
        return $this->fieldNames;
    }

    /**
     * set FieldName
     *
     * @param mixed ...$fieldNames fieldName
     *
     * @return void
     */
    public function setFieldNames(...$fieldNames)
    {
        $this->fieldNames = $fieldNames;
    }

    /**
     * called before the entity is persisted
     *
     * @param ModelQueryEvent $event event
     *
     * @return ModelQueryEvent event
     */
    public function onQuery(ModelQueryEvent $event)
    {
        if (empty($this->fieldNames)) {
            return $event;
        }

        $user = 'anonymous';

        $securityUser = $this->securityUtils->getSecurityUser();
        if ($securityUser instanceof SecurityUser) {
            $user = trim(strtolower($securityUser->getUsername()));
        }

        $builder = $event->getQueryBuilder();

        $conditions = [];
        foreach ($this->fieldNames as $fieldName) {
            $conditions[] = $builder->expr()->field($fieldName)->equals($user);
        }

        $builder->addAnd(...$conditions);

        $event->setQueryBuilder($builder);

        return $event;
    }
}
