<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class LessThan implements CriteriaInterface
{
    /**
     * @var string|Raw
     */
    private string|Raw $column;

    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * LessThan constructor.
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
        $queryBuilder->where($this->column, '<', $this->value);
    }
}
