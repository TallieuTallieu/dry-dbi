<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\StatementInterface;

abstract class BuildHandler
{
    /**
     * The table the BuildHandler works on
     * @var string
     */
    private string $table = '';

    /**
     * The output query
     * @var string
     */
    private string $query = '';

    /**
     * The output query parameters
     * @var array<int, mixed>
     */
    private array $parameters = [];

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
        return '`' . $statement . '`';
    }

    /**
     * @param mixed $statement
     * @param bool $useTablePrefix
     * @return StatementInterface
     */
    protected function createStatement(
        mixed $statement,
        bool $useTablePrefix = false
    ): StatementInterface {
        if ($statement instanceof StatementInterface) {
            return $statement;
        }

        if ($useTablePrefix) {
            return new Raw($this->withTablePrefix((string) $statement));
        }

        return new Raw('?', [$statement]);
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

        return $this->quote($table) . '.' . $this->quote($column);
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
    final protected function addToQuery(string $queryPart): void
    {
        $this->query .= $queryPart;
    }

    /**
     * @param mixed $value
     */
    final protected function addParameter(mixed $value): void
    {
        $this->parameters[] = $value;
    }

    /**
     * @param array<int, mixed> $values
     */
    final protected function addParameters(array $values): void
    {
        foreach ($values as $value) {
            $this->addParameter($value);
        }
    }

    /**
     * @return array<int, mixed>
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
