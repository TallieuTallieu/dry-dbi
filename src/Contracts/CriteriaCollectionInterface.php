<?php

namespace Tnt\Dbi\Contracts;

interface CriteriaCollectionInterface
{
    /**
     * @param CriteriaInterface $criteria
     */
    public function addCriteria(CriteriaInterface $criteria): void;

    /**
     * @return array<int, CriteriaInterface>
     */
    public function getCriteria(): array;
}
