<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 04.04.17
 * Time: 09:50
 */
namespace Graviton\ApiBundle\Service;


use Graviton\ApiBundle\Manager\DatabaseManager;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class MappingService
{
    /**
     * Meant to map the Service data with the output
     * TODO move this to a real mapper class
     * @param $data
     * @param $schema
     * @return array
     */
    public function mapData($data, $schema)
    {
        if (empty($data)) {
            return $data;
        }
        if (array_key_exists('_id', $data)) {
            return $this->fieldMapper($data);
        } else {
            foreach ($data as &$item) {
                $item = $this->fieldMapper($item);
            }
        }
        return $data;
    }
    private function fieldMapper($data)
    {
        // Use some how $this->schema to map output
        $rtn = [];
        foreach ($data as $field => $value) {
            if ('_id' == $field) {
                $rtn['id'] = $value;
            } else {
                $rtn[$field] = $value;
            }
        }

        return $rtn;
    }
}