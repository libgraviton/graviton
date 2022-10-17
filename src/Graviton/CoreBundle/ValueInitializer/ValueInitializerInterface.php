<?php

namespace Graviton\CoreBundle\ValueInitializer;

interface ValueInitializerInterface
{
    public function getInitialValue(mixed $presentValue);
}
