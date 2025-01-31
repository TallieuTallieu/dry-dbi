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

    private function prepValues() {
        return '(' . join(',',array_map(function ($value) {
            if (is_string($value)) {

                return "'$value'";
            }
            return $value;
        }, $this->value)) . ')';
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder)
    {
        $queryBuilder->where($this->column, 'IN', new Raw($this->prepValues()));
    }
}
