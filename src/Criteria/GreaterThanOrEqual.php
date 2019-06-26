<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;

class GreaterThanOrEqual implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $column;

	private $value;

	/**
	 * GreaterThanOrEqual constructor.
	 * @param string $column
	 * @param $value
	 */
	public function __construct(string $column, $value)
	{
		$this->column = $column;
		$this->value = $value;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function apply(QueryBuilder $queryBuilder)
	{
		$queryBuilder->where($this->column, '>=', $this->value);
	}
}