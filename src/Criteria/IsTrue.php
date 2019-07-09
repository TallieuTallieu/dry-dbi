<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class IsTrue implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * IsTrue constructor.
	 * @param mixed $column
	 */
	public function __construct($column)
	{
		$this->column = $column;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function apply(QueryBuilder $queryBuilder)
	{
		$queryBuilder->where($this->column, 'IS', new Raw('TRUE'));
	}
}