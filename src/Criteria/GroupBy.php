<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class GroupBy implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * GroupBy constructor.
	 * @param string $column
	 */
	public function __construct(string $column)
	{
		$this->column = $column;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function apply(QueryBuilder $queryBuilder)
	{
		$queryBuilder->groupBy($this->column);
	}
}