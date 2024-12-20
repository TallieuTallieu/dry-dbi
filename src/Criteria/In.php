<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class In implements CriteriaInterface
{
    /**
     * @var string
     */
    private $column;

    /**
     * @var array
     */
    private $value;

    /**
     * Equals constructor.
     * @param mixed $column
     * @param array $value
     */
    public function __construct($column, $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where($this->column, 'IN', new Raw('('. join(',', $this->value) . ')'));
    }
}
