<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class OrEquals implements CriteriaInterface
{
    protected $criteria;

    /**
     * $criteria = array(array($column, $value), array($column, $value), array($column, $value))
     * @param array $criteria
     */
    public function __construct($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function apply(QueryBuilder $queryBuilder)
    {
    $queryBuilder->whereGroup(function($queryBuilder) {
			foreach ($this->criteria as $curCriteria) {
				$queryBuilder->where($curCriteria[0], '=', $curCriteria[1], 'OR');
			}
        }, 'AND');
    }
}
