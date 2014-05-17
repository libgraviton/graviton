<?php

namespace Graviton\CoreBundle\Model;

use Graviton\RestBundle\Model\Doctrine\ODM as Model;
use Graviton\CoreBundle\Repository\AppRepository;

class App extends Model
{
    public function __construct(AppRepository $apps)
    {
        $this->repository = $apps;
    }
}
