<?php
namespace Graviton\RestBundle\Model;

use Graviton\RestBundle\Model\ModelDoctrine as Model;

/**
 * RestModelDoctrineFactory
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class DoctrineFactory
{
    /**
     * {@inheritDoc}
     *
     * @param String $className  Class name of model
     * @param String $connection Name of connection to use
     *
     * @return Model
     */
    public function getModelDoctrine($className, $connection = "default")
    {
        $model = new Model($className, $connection);

        return $model;
    }
}
