<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class GroupBy implements CriteriaInterface
{
    /**
     * @var string
     */
    private string|Raw $column;

    /**
     * GroupBy constructor.
     * @param string|Raw $column
     */
    public function __construct(string|Raw $column)
    {
        $this->column = $column;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->groupBy($this->column);
    }
}
