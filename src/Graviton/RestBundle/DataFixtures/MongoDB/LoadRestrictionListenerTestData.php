<?php
/**
 * fixtures fix to load a fixture field that is not exposed
 */

namespace Graviton\RestBundle\DataFixtures\MongoDB;

use GravitonDyn\TestCaseMultiTenantBundle\DataFixtures\MongoDB\LoadTestCaseMultiTenantData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoadRestrictionListenerTestData extends LoadTestCaseMultiTenantData
{

    /**
     * overrides the fixture generated, manually setting the clientId for the test
     *
     * @param mixed $record record
     *
     * @return mixed object
     */
    public function getObjectFromRecord($record)
    {
        $rec = parent::getObjectFromRecord($record);

        $clientId = null;
        foreach ($this->fixtures as $fixture) {
            if (property_exists($fixture, 'clientId') && $fixture->id == $rec->getId()) {
                $clientId = $fixture->clientId;
            }
        }

        if (!is_null($clientId)) {
            $rec->setClientId($clientId);
        }

        return $rec;
    }
}
