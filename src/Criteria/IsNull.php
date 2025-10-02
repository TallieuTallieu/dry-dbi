<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class IsNull implements CriteriaInterface
{
    /**
     * @var string
     */
    private string $column;

    /**
     * IsNull constructor.
     * @param string $column
     */
    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where($this->column, 'IS', new Raw('NULL'));
    }
}
