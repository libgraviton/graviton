<?php
/**
 * BodyChecker
 */

namespace Graviton\RestBundle\Service\BodyChecks;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
abstract readonly class BodyCheckerAbstract
{

    abstract public function check(BodyCheckData $data) : void;
}
