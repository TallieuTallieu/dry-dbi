<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Contracts\StatementInterface;

class QueryBuilder extends BuildHandler
{
    /**
     * @var callable(TableBuilder): void|null
     */
    private $create = null;

    /**
     * @var callable(TableBuilder): void|null
     */
    private $alter = null;

    /**
     * @var bool
     */
    private bool $drop = false;

    /**
     * @var string|null
     */
    private ?string $rename = null;

    /**
     * @var array<int, StatementInterface>
     */
    private array $select = [];

    /**
     * @var array<int, JoinBuilder>
     */
    private array $joins = [];

    /**
     * @var array<int, array<int, mixed>>
     */
    private array $where = [];

    /**
     * @var bool
     */
    private bool $isGroupingMode = false;

    /**
     * @var array<int, array{0: StatementInterface, 1: string}>
     */
    private array $orderBy = [];

    /**
     * @var array<int, StatementInterface>
     */
    private array $groupBy = [];

    /**
     * @var array<int, array<int, mixed>>
     */
    private array $having = [];

    /**
     * @var StatementInterface|null
     */
    private ?StatementInterface $limit = null;

    /**
     * @var StatementInterface|null
     */
    private ?StatementInterface $offset = null;

    /**
     * @param callable(TableBuilder): void $createScheme
     * @return $this
     */
    public function create(callable $createScheme): QueryBuilder
    {
        $this->create = $createScheme;
        return $this;
    }

    /**
     * @param callable(TableBuilder): void $alterScheme
     * @return $this
     */
    public function alter(callable $alterScheme): QueryBuilder
    {
        $this->alter = $alterScheme;
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function drop(): QueryBuilder
    {
        $this->drop = true;
        return $this;
    }

    /**
     * @param string $newTableName
     * @return QueryBuilder
     */
    public function rename(string $newTableName): QueryBuilder
    {
        $this->rename = $newTableName;
        return $this;
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function select(string|Raw $column): QueryBuilder
    {
        $this->select[] = $this->createStatement($column, true);
        return $this;
    }

    /**
     * @param string|null $table
     * @return QueryBuilder
     */
    public function selectAll(?string $table = null): QueryBuilder
    {
        $this->select(
            new Raw($this->quote($table ?: $this->getTable()) . '.*')
        );
        return $this;
    }

    /**
     * @param string|Raw $statement
     * @param string $alias
     * @return QueryBuilder
     */
    public function selectAs(string|Raw $statement, string $alias): QueryBuilder
    {
        $statement = $this->createStatement($statement, true);
        $aliasStatement = new Raw(
            $statement->getValue() . ' AS ' . $alias,
            $statement->getBindings()
        );
        $this->select($aliasStatement);
        return $this;
    }

    /**
     * @param string|Raw $field
     * @param string $operator
     * @param mixed $value
     * @param string $connectBefore
     * @return QueryBuilder
     */
    public function where(
        string|Raw $field,
        string $operator,
        mixed $value,
        string $connectBefore = 'AND'
    ): QueryBuilder {
        $whereStatement = [
            $this->createStatement($field, true),
            $operator,
            $this->createStatement($value),
            $connectBefore,
        ];

        if ($this->isGroupingMode && count($this->where) > 0) {
            $lastIndex = count($this->where) - 1;
            if (
                is_array($this->where[$lastIndex]) &&
                isset($this->where[$lastIndex][0]) &&
                is_array($this->where[$lastIndex][0])
            ) {
                $this->where[$lastIndex][0][] = $whereStatement;
            }
            return $this;
        }

        $this->where[] = $whereStatement;
        return $this;
    }

    /**
     * @param callable(QueryBuilder): void $call
     * @param string $connectBefore
     */
    public function whereGroup(
        callable $call,
        string $connectBefore = 'AND'
    ): void {
        $this->isGroupingMode = true;

        $this->where[] = [[], $connectBefore];

        call_user_func_array($call, [$this]);
        $this->isGroupingMode = false;
    }

    /**
     * @param string|Raw $field
     * @param string $operator
     * @param mixed $value
     * @param string $connectBefore
     * @return QueryBuilder
     */
    public function having(
        string|Raw $field,
        string $operator,
        mixed $value,
        string $connectBefore = 'AND'
    ): QueryBuilder {
        $havingStatement = [
            $this->createStatement($field, true),
            $operator,
            $this->createStatement($value),
            $connectBefore,
        ];

        if ($this->isGroupingMode && count($this->having) > 0) {
            $lastIndex = count($this->having) - 1;
            if (
                is_array($this->having[$lastIndex]) &&
                isset($this->having[$lastIndex][0]) &&
                is_array($this->having[$lastIndex][0])
            ) {
                $this->having[$lastIndex][0][] = $havingStatement;
            }
            return $this;
        }

        $this->having[] = $havingStatement;
        return $this;
    }

    /**
     * @param callable(QueryBuilder): void $call
     * @param string $connectBefore
     */
    public function havingGroup(
        callable $call,
        string $connectBefore = 'AND'
    ): void {
        $this->isGroupingMode = true;

        $this->having[] = [[], $connectBefore];

        call_user_func_array($call, [$this]);
        $this->isGroupingMode = false;
    }

    /**
     * @param string|Raw $column
     * @param string $sortMethod
     * @return QueryBuilder
     */
    public function orderBy(
        string|Raw $column,
        string $sortMethod = 'ASC'
    ): QueryBuilder {
        $field = $this->createStatement($column, true);

        $this->orderBy = array_filter($this->orderBy, function ($value) use (
            $field
        ) {
            return $value[0]->getValue() !== $field->getValue();
        });

        $this->orderBy[] = [$field, $sortMethod];
        return $this;
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function groupBy(string|Raw $column): QueryBuilder
    {
        $this->groupBy[] = $this->createStatement($column, true);
        return $this;
    }

    /**
     * @param int|Raw $limit
     * @return QueryBuilder
     */
    public function limit(int|Raw $limit): QueryBuilder
    {
        $this->limit = $this->createStatement($limit);
        return $this;
    }

    /**
     * @param int|Raw $offset
     * @return QueryBuilder
     */
    public function offset(int|Raw $offset): QueryBuilder
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
        foreach ($this->joins as $join) {
            if ($join->getTable() === $table) {
                return $join;
            }
        }

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
    private function buildHandler(
        BuildHandler $buildHandler,
        string $connector = ' '
    ): void {
        $buildHandler->build();

        $query = $buildHandler->getQuery();
        $params = $buildHandler->getParameters();

        $this->addToQuery($connector . $query);

        foreach ($params as $param) {
            $this->addParameter($param);
        }
    }

    /**
     *
     */
    private function buildJoins(): void
    {
        foreach ($this->joins as $join) {
            $this->buildHandler($join);
        }
    }

    /**
     *
     */
    private function buildWhere(): void
    {
        $this->buildConditions($this->where, 'WHERE');
    }

    /**
     * @param array<int, mixed> $statements
     * @param string $keyword
     */
    private function buildConditions(array $statements, string $keyword): void
    {
        if (!count($statements)) {
            return;
        }

        $this->addToQuery(' ' . $keyword . ' ');

        $i = 0;
        foreach ($statements as $statement) {
            $isGroup = is_array($statement[0]);

            if ($isGroup) {
                if ($i !== 0) {
                    $this->addToQuery(' ' . $statement[1] . ' ');
                }

                $this->addToQuery(' ( ');

                $j = 0;
                foreach ($statement[0] as $subStatement) {
                    $this->buildCondition($subStatement, $j === 0);
                    $j++;
                }

                $this->addToQuery(' ) ');
            } else {
                $this->buildCondition($statement, $i === 0);
            }

            $i++;
        }
    }

    /**
     * @param array<int, mixed> $statement
     * @param bool $isFirst
     */
    private function buildCondition(
        array $statement,
        bool $isFirst = true
    ): void {
        $column = $statement[0];
        $operator = $statement[1];
        $value = $statement[2];

        if (!$isFirst) {
            $this->addToQuery(' ' . $statement[3] . ' ');
        }

        $this->addToQuery(
            $column->getValue() . ' ' . $operator . ' ' . $value->getValue()
        );
        $this->addParameters($column->getBindings());
        $this->addParameters($value->getBindings());
    }

    /**
     *
     */
    private function buildSelect(): void
    {
        $selectStatement = [];

        foreach ($this->select as $select) {
            $selectStatement[] = $select->getValue();
            $this->addParameters($select->getBindings());
        }

        $this->addToQuery(
            'SELECT ' .
                implode(', ', $selectStatement) .
                ' FROM ' .
                $this->quote($this->getTable())
        );
    }

    /**
     *
     */
    private function buildOrderBy(): void
    {
        if (count($this->orderBy)) {
            $orderByStatements = [];

            foreach ($this->orderBy as $orderBy) {
                $orderByStatement = $orderBy[0];

                $orderByStatements[] =
                    $orderByStatement->getValue() . ' ' . $orderBy[1];
                $this->addParameters($orderByStatement->getBindings());
            }

            $this->addToQuery(' ORDER BY ' . implode(', ', $orderByStatements));
        }
    }

    /**
     *
     */
    private function buildGroupBy(): void
    {
        if (count($this->groupBy)) {
            $groupByStatements = [];

            foreach ($this->groupBy as $groupBy) {
                $groupByStatements[] = $groupBy->getValue();
                $this->addParameters($groupBy->getBindings());
            }

            $this->addToQuery(' GROUP BY ' . implode(', ', $groupByStatements));
        }
    }

    /**
     *
     */
    private function buildHaving(): void
    {
        $this->buildConditions($this->having, 'HAVING');
    }

    /**
     *
     */
    private function buildLimitOffset(): void
    {
        $limit = $this->limit;
        if ($limit !== null) {
            $this->addToQuery(' LIMIT ' . $limit->getValue());
            $this->addParameters($limit->getBindings());

            $offset = $this->offset;
            if ($offset !== null) {
                $this->addToQuery(' OFFSET ' . $offset->getValue());
                $this->addParameters($offset->getBindings());
            }
        }
    }

    /**
     *
     */
    private function buildCreate(): void
    {
        $tableBuilder = new TableBuilder();
        $tableBuilder->table($this->getTable());
        call_user_func($this->create, $tableBuilder);
        $tableBuilder->build();

        $this->addToQuery('CREATE TABLE ' . $this->quote($this->getTable()));
        $this->addToQuery(' (' . $tableBuilder->getQuery() . ')');
        $this->addToQuery(' COLLATE \'utf8_unicode_ci\'');
    }

    /**
     *
     */
    private function buildAlter(): void
    {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table($this->getTable());
        call_user_func($this->alter, $tableBuilder);
        $tableBuilder->build();

        $this->addToQuery('ALTER TABLE ' . $this->quote($this->getTable()));
        $this->addToQuery(' ' . $tableBuilder->getQuery());
    }

    /**
     *
     */
    private function buildDrop(): void
    {
        $this->addToQuery('DROP TABLE ' . $this->quote($this->getTable()));
    }

    /**
     *
     */
    private function buildRename(): void
    {
        $this->addToQuery(
            'RENAME TABLE ' .
                $this->quote($this->getTable()) .
                ' TO ' .
                $this->quote($this->rename)
        );
    }

    /**
     * @return bool
     */
    private function isSelectQuery(): bool
    {
        return count($this->select) > 0;
    }

    /**
     * @return bool
     */
    private function isCreateQuery(): bool
    {
        return $this->create !== null;
    }

    /**
     * @return bool
     */
    private function isAlterQuery(): bool
    {
        return $this->alter !== null;
    }

    /**
     * @return bool
     */
    private function isDropQuery(): bool
    {
        return $this->drop;
    }

    /**
     * @return bool
     */
    private function isRenameQuery(): bool
    {
        return $this->rename !== null;
    }

    /**
     * @return void
     */
    public function build(): void
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

        if ($this->isCreateQuery()) {
            $this->buildCreate();
            return;
        }

        if ($this->isAlterQuery()) {
            $this->buildAlter();
            return;
        }

        if ($this->isDropQuery()) {
            $this->buildDrop();
            return;
        }

        if ($this->isRenameQuery()) {
            $this->buildRename();
            return;
        }
    }
}
