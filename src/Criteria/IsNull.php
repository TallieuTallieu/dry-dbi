<?php

namespace Tnt\Dbi\Criteria;

use Tnt\Dbi\Contracts\CriteriaInterface;
use Tnt\Dbi\QueryBuilder;
use Tnt\Dbi\Raw;

class IsNull implements CriteriaInterface
{
	/**
	 * @var string
	 */
	private $field;

	/**
	 * IsNull constructor.
	 * @param string $field
	 */
	public function __construct(string $field)
	{
		$this->field = $field;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function apply(QueryBuilder $queryBuilder)
	{
		$queryBuilder->where($this->field, 'IS', new Raw( 'NULL' ));
	}
}