<?php

namespace Graviton\ApiBundle\Controller;

use Graviton\ApiBundle\Service\ApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class RestController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ApiService
     */
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }


    public function indexAction()
    {
        $response = new JsonResponse();
        $data = $this->apiService->getRoutes();
        $response->setData($data);

        return $response;
    }


    public function schemaAction()
    {
        $response = new JsonResponse();
        $data = $this->apiService->getSchema();
        $response->setData($data);

        return $response;
    }


    public function getAction()
    {
        $response = new JsonResponse();
        $data = $this->apiService->getData();
        $response->setData($data);

        return $response;
    }

    public function optionsAction()
    {
        $response = new JsonResponse();
        $response->setData(['something in options']);

        return $response;
    }


    public function putAction()
    {
        $response = new JsonResponse();
        $response->setData(['something in put']);

        return $response;
    }


    public function patchAction()
    {
        $response = new JsonResponse();
        $response->setContent(['something in patch']);

        return $response;
    }
}
