<?php

namespace Tnt\Dbi;

class QueryBuilder extends BuildHandler
{
	/**
	 * @var array $select
	 */
	private $select = [];

	/**
	 * @var array
	 */
	private $joins = [];

	/**
	 * @var array
	 */
	private $where = [];

	/**
	 * @var array
	 */
	private $orderBy = [];

	/**
	 * @var array
	 */
	private $groupBy = [];

	/**
	 * @var array
	 */
	private $having = [];

	/**
	 * @var int
	 */
	private $limit;

	/**
	 * @var
	 */
	private $offset;

	/**
	 * @param $column
	 * @return QueryBuilder
	 */
	public function select($column): QueryBuilder
	{
		$this->select[] = $this->createStatement($column, true);
		return $this;
	}

	/**
	 * @param null $table
	 * @return QueryBuilder
	 */
	public function selectAll($table = null): QueryBuilder
	{
		$this->select(new Raw($this->quote($table ?: $this->getTable()).'.*'));
		return $this;
	}

	/**
	 * @param $statement
	 * @param string $alias
	 * @return QueryBuilder
	 */
	public function selectAs($statement, string $alias): QueryBuilder
	{
		$statement = $this->createStatement($statement, true);
		$aliasStatement = new Raw($statement->getValue().' AS '.$alias, $statement->getBindings());
		$this->select($aliasStatement);
		return $this;
	}

	/**
	 * @param $field
	 * @param string $operator
	 * @param $value
	 * @return QueryBuilder
	 */
	public function where($field, string $operator, $value): QueryBuilder
	{
		$this->where[] = [$this->createStatement($field, true), $operator, $this->createStatement($value),];
		return $this;
	}

	/**
	 * @param $column
	 * @param string $sortMethod
	 * @return QueryBuilder
	 */
	public function orderBy($column, string $sortMethod = 'ASC'): QueryBuilder
	{
		$field = $this->createStatement($column, true);

		$this->orderBy = array_filter($this->orderBy, function($value) use($field) {

			return ($value[0]->getValue() !== $field->getValue());
		});

		$this->orderBy[] = [$field, $sortMethod,];
		return $this;
	}

	/**
	 * @param $column
	 * @return QueryBuilder
	 */
	public function groupBy($column): QueryBuilder
	{
		$this->groupBy[] = $this->createStatement($column, true);
		return $this;
	}

	/**
	 * @param $field
	 * @param string $operator
	 * @param $value
	 * @return QueryBuilder
	 */
	public function having($field, string $operator, $value): QueryBuilder
	{
		$this->having[] = [$this->createStatement($field, true), $operator, $this->createStatement($value),];
		return $this;
	}

	/**
	 * @param $limit
	 * @return QueryBuilder
	 */
	public function limit($limit): QueryBuilder
	{
		$this->limit = $this->createStatement($limit);
		return $this;
	}

	/**
	 * @param $offset
	 * @return QueryBuilder
	 */
	public function offset($offset): QueryBuilder
	{
		$this->offset = $this->createStatement($offset);
		return $this;
	}

	/**
	 * @param string $table
	 * @return JoinBuilder
	 */
	public function innerJoin(string $table): JoinBuilder
	{
		return $this->join('inner', $table);
	}

	/**
	 * @param string $table
	 * @return JoinBuilder
	 */
	public function rightJoin(string $table): JoinBuilder
	{
		return $this->join('right', $table);
	}

	/**
	 * @param string $table
	 * @return JoinBuilder
	 */
	public function leftJoin(string $table): JoinBuilder
	{
		return $this->join('left', $table);
	}

	/**
	 * @param string $type
	 * @param string $table
	 * @return JoinBuilder
	 */
	private function join(string $type, string $table): JoinBuilder
	{
		$join = new JoinBuilder();
		$join->table($table);
		$join->setType($type);
		$this->joins[] = $join;
		return $join;
	}

	/**
	 * @param BuildHandler $buildHandler
	 * @param string $connector
	 */
	private function buildHandler(BuildHandler $buildHandler, string $connector = ' ')
	{
		$buildHandler->build();

		$query = $buildHandler->getQuery();
		$params = $buildHandler->getParameters();

		$this->addToQuery($connector.$query);

		foreach ($params as $param) {
			$this->addParameter($param);
		}
	}

	/**
	 *
	 */
	private function buildJoins()
	{
		foreach ($this->joins as $join) {
			$this->buildHandler($join);
		}
	}

	/**
	 *
	 */
	private function buildWhere()
	{
		if (!count($this->where)) {
			return;
		}

		$whereStatements = [];

		foreach ($this->where as $where) {

			$column = $where[0];
			$operator = $where[1];
			$value = $where[2];

			$whereStatements[] = $column->getValue() . ' ' . $operator . ' ' . $value->getValue();
			$this->addParameters($column->getBindings());
			$this->addParameters($value->getBindings());
		}

		$this->addToQuery(' WHERE ' . implode(' AND ', $whereStatements));
	}

	/**
	 *
	 */
	private function buildSelect()
	{
		$selectStatement = [];

		foreach ($this->select as $select) {
			$selectStatement[] = $select->getValue();
			$this->addParameters($select->getBindings());
		}

		$this->addToQuery('SELECT '.implode(', ', $selectStatement).' FROM '.$this->quote($this->getTable()));
	}

	/**
	 *
	 */
	private function buildOrderBy()
	{
		if (count($this->orderBy)) {

			$orderByStatements = [];

			foreach ($this->orderBy as $orderBy) {

				$orderByStatement = $orderBy[0];

				$orderByStatements[] = $orderByStatement->getValue().' '.$orderBy[1];
				$this->addParameters($orderByStatement->getBindings());
			}

			$this->addToQuery(' ORDER BY '.implode(', ', $orderByStatements));
		}
	}

	/**
	 *
	 */
	private function buildGroupBy()
	{
		if (count($this->groupBy)) {

			$groupByStatements = [];

			foreach ($this->groupBy as $groupBy) {

				$groupByStatements[] = $groupBy->getValue();
				$this->addParameters($groupBy->getBindings());
			}

			$this->addToQuery(' GROUP BY '.implode(', ', $groupByStatements));
		}
	}

	/**
	 *
	 */
	private function buildHaving()
	{
		if (!count($this->having)) {
			return;
		}

		$havingStatements = [];

		foreach ($this->having as $having) {

			$column = $having[0];
			$operator = $having[1];
			$value = $having[2];

			$havingStatements[] = $column->getValue() . ' ' . $operator . ' ' . $value->getValue();

			$this->addParameters($column->getBindings());
			$this->addParameters($value->getBindings());
		}

		$this->addToQuery(' HAVING ' . implode(' AND ', $havingStatements));
	}

	/**
	 *
	 */
	private function buildLimitOffset()
	{
		if ($this->limit) {

			$this->addToQuery(' LIMIT '.$this->limit->getValue());
			$this->addParameters($this->limit->getBindings());

			if ($this->offset) {
				$this->addToQuery(' OFFSET '.$this->offset->getValue());
				$this->addParameters($this->offset->getBindings());
			}
		}
	}

	/**
	 * @return int
	 */
	private function isSelectQuery()
	{
		return (count($this->select));
	}

	/**
	 * @return mixed|void
	 */
	public function build()
	{
		if ($this->isSelectQuery()) {

			$this->buildSelect();
			$this->buildJoins();
			$this->buildWhere();
			$this->buildGroupBy();
			$this->buildHaving();
			$this->buildOrderBy();
			$this->buildLimitOffset();

			return;
		}
	}
}