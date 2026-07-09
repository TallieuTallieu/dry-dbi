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
    private string|Raw $column;

    /**
     * @var array<int, mixed>
     */
    private array $value;

    /**
     * Equals constructor.
     * @param string|Raw $column
     * @param array<int, mixed> $value
     */
    public function __construct(string|Raw $column, array $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $placeholders = implode(',', array_fill(0, count($this->value), '?'));
        $queryBuilder->where($this->column, 'IN', new Raw("($placeholders)", array_values($this->value)));
    }
}
