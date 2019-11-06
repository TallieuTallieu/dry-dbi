<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\CriteriaCollectionInterface;
use Tnt\Dbi\Contracts\CriteriaInterface;

class CriteriaCollection implements CriteriaCollectionInterface
{
    /**
     * @var array
     */
    private $criteria = [];

    /**
     * @param CriteriaInterface $criteria
     */
    public function addCriteria(CriteriaInterface $criteria)
    {
        $this->criteria[] = $criteria;
    }

    /**
     * @return array
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}