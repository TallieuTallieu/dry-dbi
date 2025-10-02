<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class LessThanOrEqual implements CriteriaInterface
{
    /**
     * @var string
     */
    private string $column;

    /**
     * @var mixed
     */
    private mixed $value;

    /**
     * LessThanOrEqual constructor.
     * @param string $column
     * @param mixed $value
     */
    public function __construct(string $column, mixed $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where($this->column, '<=', $this->value);
    }
}
