<?php

namespace Tnt\Dbi\Contracts;

use Tnt\Dbi\QueryBuilder;

interface CriteriaInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void;
}
