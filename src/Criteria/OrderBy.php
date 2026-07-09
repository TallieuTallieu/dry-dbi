<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class OrderBy implements CriteriaInterface
{
    /**
     * @var string|Raw
     */
    private string|Raw $column;

    /**
     * @var string
     */
    private string $order;

    /**
     * OrderBy constructor.
     * @param string|Raw $column
     * @param string $order
     */
    public function __construct(string|Raw $column, string $order = 'ASC')
    {
        $this->column = $column;
        $this->order = $order;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->orderBy($this->column, $this->order);
    }
}
