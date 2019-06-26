<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\StatementInterface;

abstract class BuildHandler
{
	/**
	 * The table the BuildHandler works on
	 * @var string
	 */
	private $table;

	/**
	 * The output query
	 * @var string
	 */
	private $query = '';

	/**
	 * The output query parameters
	 * @var array
	 */
	private $parameters = [];

	/**
	 * @return mixed
	 */
	abstract public function build();

	/**
	 * @param string $statement
	 * @return string
	 */
	final protected function quote(string $statement)
	{
		return '`'.$statement.'`';
	}

	/**
	 * @param $statement
	 * @param bool $useTablePrefix
	 * @return StatementInterface
	 */
	protected function createStatement($statement, $useTablePrefix = false): StatementInterface
	{
		if ($statement instanceof StatementInterface) {
			return $statement;
		}

		if ($useTablePrefix) {
			return new Raw($this->withTablePrefix($statement));
		}

		return new Raw('?', [$statement, ]);
	}

	/**
	 * @param string $columnName
	 * @return string
	 */
	protected function withTablePrefix(string $columnName): string
	{
		$table = $this->table;
		$column = $columnName;
		$parts = explode('.', $columnName, 2);

		if (count($parts) > 1) {
			$table = $parts[0];
			$column = $parts[1];
		}

		return $this->quote($table).'.'.$this->quote($column);
	}

	/**
	 * @return string
	 */
	final public function getTable(): string
	{
		return $this->table;
	}

	/**
	 * @param string $tableName
	 * @return $this
	 */
	final public function table(string $tableName)
	{
		$this->table = $tableName;
		return $this;
	}

	/**
	 * @param string $queryPart
	 */
	final protected function addToQuery(string $queryPart)
	{
		$this->query .= $queryPart;
	}

	/**
	 * @param $value
	 */
	final protected function addParameter($value)
	{
		$this->parameters[] = $value;
	}

	/**
	 * @param array $values
	 */
	final protected function addParameters(array $values)
	{
		foreach ($values as $value) {
			$this->addParameter($value);
		}
	}

	/**
	 * @return array
	 */
	final public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @return string
	 */
	final public function getQuery(): string
	{
		return $this->query;
	}
}