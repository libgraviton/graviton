<?php
namespace Graviton\RestBundle\Model;

use Graviton\RestBundle\Model\ModelDoctrine as Model;

/**
 * MapperInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestModelDoctrineFactory
{
	function getModelDoctrine($className, $connection = "default")
	{
		$model = new Model($className, $connection);
		
		return $model;
	}
}