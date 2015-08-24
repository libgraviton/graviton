<?php

namespace Graviton\MessageBundle\Service;

use Graviton\MessageBundle\Document\JobStatus;
use Graviton\MessageBundle\Exception\UnknownRoutingKeyException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Graviton\MessageBundle\Document\Job;

class JobProducer extends Producer
{

    public $replyTo = 'graviton.message.status';

    public $registeredRoutingKeys = array();

    protected $jobRepository = null;

    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $additionalProperties['message_id'] = $routingKey;
        $additionalProperties['correlation_id'] = $routingKey;
        $additionalProperties['reply_to'] = $this->replyTo;
        $this->validateRoutingKey($routingKey);
        $this->createJobStatus();
        return parent::publish($msgBody, $routingKey, $additionalProperties);
    }

    public function setJobRepository($repository)
    {
        $this->jobRepository = $repository;
    }

    public function validateRoutingKey($routingKey)
    {
        if (!in_array($routingKey, $this->registeredRoutingKeys)) {
            throw new UnknownRoutingKeyException($routingKey);
        }
    }

    protected function createJobStatus()
    {
        $entity = new JobStatus();
        $manager = $this->jobRepository->get('graviton.message.repository.job')->getDocumentManager();
        $manager->persist($entity);
        $manager->flush();
        return $manager->find(get_class($entity), $entity->getId());
    }
}
