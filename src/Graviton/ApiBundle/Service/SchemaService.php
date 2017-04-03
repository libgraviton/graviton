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

class SchemaService
{
    /** @var ConfigService */
    protected $configService;

    public function __construct(
        $configService
    ) {
        $this->configService = $configService;
    }

    /**
     * Should return the full schema tree from cached location, build and save.
     *
     * @param $classId
     * @return mixed
     */
    public function getSchema($classId)
    {
        $mainSchema = $this->configService->getJsonFromFile('schema/' . $classId .'.json');
            // we should load several levels ...
        return $mainSchema;
    }

    /**
     * Should build a full schema tree
     * @param $classId
     */
    private function buildSchemaTree($classId)
    {

    }

}