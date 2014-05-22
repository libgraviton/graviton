<?php
namespace Graviton\RestBundle\Pager;

interface PagerInterface
{
    /**
     * Get calculated offset
     *
     * @return Number $offset Calculated offset
     */
    public function getOffset();

    public function getNextPage();

    public function getPrevPage();

    public function getLastPage();
}
