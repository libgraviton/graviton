<?php

namespace Graviton\ApiBundle\Manager;


class DatabaseManager
{
    /** @var string */
    protected $databseUri;

    /** @var string */
    protected $databaseName;

    /** @var \MongoClient */
    protected $dbClient;

    public function __construct(
        $dbUrl,
        $dbName
    ) {
        $this->databseUri = $dbUrl;
        $this->databaseName = $dbName;
    }

    /**
     * Connect to DB
     *
     * @throws \MongoConnectionException
     * @return \MongoClient
     */
    private function getClient()
    {
        if (!$this->dbClient) {
            $this->dbClient = new \MongoClient($this->databseUri, ['connect' => false]);
            $this->dbClient->connect();
        }
        return $this->dbClient;
    }

    private function find($collection, $filter = [], $sort = [], $limit = 1, $offset = 0)
    {
        /** @var \MongoCollection $collection */
        $collection = $this->getClient()->{$this->databaseName}->{$collection};

        $options = [];//['sort' => ['catid' => 1], 'limit' => 10];
        /** @var \MongoCursor $cursor */
        $cursor = $collection->find($filter, $options);
        return iterator_to_array($cursor, false);
    }

    public function findOne($collection, $id)
    {
        $filter = ['_id' => (string) $id];
        $data = $this->find($collection, $filter);
        if ($data) {
            return $data[0];
        }
        return [];
    }

    public function findAll($collection)
    {
        $data = $this->find($collection, [], [], 10);
        return $data;
    }
}
