<?php
/**
 * Validates the correct behavior of the voter
 */
namespace Graviton\SecurityBundle\Voter;

use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ServiceAllowedVoterTest extends GravitonTestCase
{
    /**
     * validate supportsAttribute
     *
     * @return void
     */
    public function testSupportsAttribute()
    {
        $voter = new ServiceAllowedVoter();

        $this->assertTrue($voter->supportsAttribute('view'));
    }

    /**
     * validate supportsClass
     *
     * @return void
     */
    public function testSupportsClass()
    {
        $voter = new ServiceAllowedVoter();

        $this->assertTrue($voter->supportsClass('\stdClass'));
    }

    /**
     * validate supportsClass
     *
     * @return void
     */
    public function testVote()
    {
        $voter = new ServiceAllowedVoter();


    }
}
