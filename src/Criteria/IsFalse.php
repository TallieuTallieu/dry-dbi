<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class IsFalse implements CriteriaInterface
{
    /**
     * @var string
     */
    private string|Raw $column;

    /**
     * IsFalse constructor.
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
        $queryBuilder->where($this->column, 'IS', new Raw('FALSE'));
    }
}
