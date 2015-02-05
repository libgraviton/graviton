<?php

namespace Graviton\SecurityBundle\Authentication\Strategies;


use Symfony\Component\HttpFoundation\Request;

interface StrategyInterface
{
    /**
     * Applies the defined strategy on the provided request.
     *
     * @param Request $request
     *
     * @return string
     */
    public function apply(Request $request);

}
