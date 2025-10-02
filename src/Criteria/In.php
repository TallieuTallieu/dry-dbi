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
    private string $column;

    /**
     * @var array<int, mixed>
     */
    private array $value;

    /**
     * Equals constructor.
     * @param string $column
     * @param array<int, mixed> $value
     */
    public function __construct(string $column, array $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    private function prepValues(): string
    {
        return '(' .
            join(
                ',',
                array_map(function ($value) {
                    if (is_string($value)) {
                        return "'$value'";
                    }
                    return $value;
                }, $this->value)
            ) .
            ')';
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->where($this->column, 'IN', new Raw($this->prepValues()));
    }
}
