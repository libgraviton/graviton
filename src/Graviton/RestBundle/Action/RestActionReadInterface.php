<?php
namespace Graviton\RestBundle\Action;

/**
 * RestActionReadInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface RestActionReadInterface
{
    public function getOne($id, $request, $model);
    
    public function getAll($request, $model);
}
