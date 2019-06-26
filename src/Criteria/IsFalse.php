<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class IsFalse implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * IsFalse constructor.
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
		$queryBuilder->where($this->column, 'IS', new Raw('FALSE'));
	}
}