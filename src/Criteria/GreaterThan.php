<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class GreaterThan implements CriteriaInterface
{
    /**
     * @var string
     */
    private string|Raw $column;

    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * GreaterThan constructor.
     * @param string|Raw $column
     * @param mixed $value
     */
    public function __construct(string|Raw $column, mixed $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where($this->column, '>', $this->value);
    }
}
