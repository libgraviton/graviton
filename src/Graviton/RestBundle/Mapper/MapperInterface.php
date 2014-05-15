<?php
namespace Graviton\RestBundle\Mapper;

/**
 * MapperInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface MapperInterface
{
    public function add($id, $value);
    
    public function remove($id);
    
    public function match($id, $byValue = false);
}
