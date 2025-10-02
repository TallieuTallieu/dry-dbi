<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class OrEquals implements CriteriaInterface
{
    /**
     * @var array<int, array{0: string, 1: mixed}>
     */
    protected array $criteria;

    /**
     * $criteria = array(array($column, $value), array($column, $value), array($column, $value))
     * @param array<int, array{0: string, 1: mixed}> $criteria
     */
    public function __construct(array $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->whereGroup(function ($queryBuilder) {
            foreach ($this->criteria as $curCriteria) {
                $queryBuilder->where(
                    $curCriteria[0],
                    '=',
                    $curCriteria[1],
                    'OR'
                );
            }
        }, 'AND');
    }
}
