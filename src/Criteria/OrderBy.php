<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class OrderBy implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var string
	 */
	private $order;

	/**
	 * OrderBy constructor.
	 * @param string $column
	 * @param string $order
	 */
	public function __construct(string $column, string $order = 'ASC')
	{
		$this->column = $column;
		$this->order = $order;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function apply(QueryBuilder $queryBuilder)
	{
		$queryBuilder->orderBy($this->column, $this->order);
	}
}