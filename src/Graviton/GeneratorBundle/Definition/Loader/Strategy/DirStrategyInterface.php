<?php
/**
 * common interface for dir/scan strategies
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface DirStrategyInterface
{
    /**
     * @param string|null $input input value
     * @param array       $file  matched file
     *
     * @return boolean
     */
    public function isValid($input, $file);
}
