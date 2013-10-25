<?php
namespace Graviton\RestBundle\Action;

/**
 * RestActionWriteInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface RestActionWriteInterface
{
	public function create($request, $model);
	
	public function update($id, $request, $model);
	
	public function delete($id, $model);
}