<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\CriteriaCollectionInterface;
use Tnt\Dbi\Contracts\CriteriaInterface;

class CriteriaCollection implements CriteriaCollectionInterface
{
    /**
     * @var array<int, CriteriaInterface>
     */
    private array $criteria = [];

    /**
     * @param CriteriaInterface $criteria
     */
    public function addCriteria(CriteriaInterface $criteria): void
    {
        $this->criteria[] = $criteria;
    }

    /**
     * @return array<int, CriteriaInterface>
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
