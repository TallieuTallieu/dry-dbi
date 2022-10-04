<?php

namespace Tnt\Dbi;

class QueryBuilder extends BuildHandler
{
    /**
     * @var callable $create
     */
    private $create;

    /**
     * @var callable $alter
     */
    private $alter;

    /**
     * @var $drop
     */
    private $drop;

    /**
     * @var array $select
     */
    private $select = [];

    /**
     * @var array $raw
     */
    private $raw = [];

    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var array
     */
    private $where = [];

    /**
     * @var bool $isGroupingMode
     */
    private $isGroupingMode = false;

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
     * @return $this
     */
    public function create(callable $createScheme): QueryBuilder
    {
        $this->create = $createScheme;
        return $this;
    }

    /**
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
     * @param null $table
     * @return QueryBuilder
     */
    public function raw($raw): QueryBuilder
    {
        $this->raw[] = $raw;

        return $this;
    }

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
     * @param $connectBefore
     * @return QueryBuilder
     */
    public function where($field, string $operator, $value, string $connectBefore = 'AND'): QueryBuilder
    {
        $whereStatement = [
            $this->createStatement($field, true),
            $operator,
            $this->createStatement($value),
            $connectBefore,
        ];

        if ($this->isGroupingMode) {
            $this->where[count($this->where)-1][0][] = $whereStatement;
            return $this;
        }

        $this->where[] = $whereStatement;
        return $this;
    }

    /**
     * @param callable $call
     * @param $connectBefore
     */
    public function whereGroup(callable $call, string $connectBefore = 'AND')
    {
        $this->isGroupingMode = true;

        $this->where[] = [
            [],
            $connectBefore,
        ];

        call_user_func_array($call, [$this, ]);
        $this->isGroupingMode = false;
    }

    /**
     * @param $field
     * @param string $operator
     * @param $value
     * @param $connectBefore
     * @return QueryBuilder
     */
    public function having($field, string $operator, $value, string $connectBefore = 'AND'): QueryBuilder
    {
        $havingStatement = [
            $this->createStatement($field, true),
            $operator,
            $this->createStatement($value),
            $connectBefore,
        ];

        if ($this->isGroupingMode) {
            $this->having[count($this->having)-1][0][] = $havingStatement;
            return $this;
        }

        $this->having[] = $havingStatement;
        return $this;
    }

    /**
     * @param callable $call
     * @param $connectBefore
     */
    public function havingGroup(callable $call, string $connectBefore = 'AND')
    {
        $this->isGroupingMode = true;

        $this->having[] = [
            [],
            $connectBefore,
        ];

        call_user_func_array($call, [$this, ]);
        $this->isGroupingMode = false;
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
        $this->buildConditions($this->where, 'WHERE');
    }

    private function buildConditions(array $statements, string $keyword)
    {
        if (!count($statements)) {
            return;
        }

        $this->addToQuery(' '.$keyword.' ');

        $i = 0;
        foreach ($statements as $statement) {

            $isGroup = (is_array($statement[0]));

            if ($isGroup) {

                if ($i !== 0) {
                    $this->addToQuery(' '.$statement[1].' ');
                }

                $this->addToQuery(' ( ');

                $j = 0;
                foreach ($statement[0] as $subStatement) {
                    $this->buildCondition($subStatement, ($j === 0));
                    $j++;
                }

                $this->addToQuery(' ) ');

            } else {

                $this->buildCondition($statement, ($i === 0));
            }

            $i++;
        }
    }

    /**
     * @param array $statement
     * @param bool $isFirst
     */
    private function buildCondition(array $statement, bool $isFirst = true)
    {
        $column = $statement[0];
        $operator = $statement[1];
        $value = $statement[2];

        if (! $isFirst) {
            $this->addToQuery(' '.$statement[3].' ');
        }

        $this->addToQuery($column->getValue().' '.$operator.' '.$value->getValue());
        $this->addParameters($column->getBindings());
        $this->addParameters($value->getBindings());
    }

    /**
     *
     */
    private function buildRaw()
    {
        $rawStatement = [];

        foreach ($this->raw as $raw) {
            $rawStatement[] = $raw->getValue();

            $this->addParameters($raw->getBindings());
        }

        $this->addToQuery(implode('; ', $rawStatement));
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
        $this->buildConditions($this->having, 'HAVING');
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
     *
     */
    private function buildCreate()
    {
        $tableBuilder = new TableBuilder();
        $tableBuilder->table($this->getTable());
        call_user_func($this->create, $tableBuilder);
        $tableBuilder->build();

        $this->addToQuery('CREATE TABLE '.$this->quote($this->getTable()));
        $this->addToQuery(' ('.$tableBuilder->getQuery().')');
        $this->addToQuery(' COLLATE \'utf8_unicode_ci\'');
    }

    /**
     *
     */
    private function buildAlter()
    {
        $tableBuilder = new TableBuilder(true);
        $tableBuilder->table($this->getTable());
        call_user_func($this->alter, $tableBuilder);
        $tableBuilder->build();

        $this->addToQuery('ALTER TABLE '.$this->quote($this->getTable()));
        $this->addToQuery(' '.$tableBuilder->getQuery());
    }

    /**
     *
     */
    private function buildDrop()
    {
        $this->addToQuery('DROP TABLE '.$this->quote($this->getTable()));
    }

    /**
     * @return bool
     */
    private function isSelectQuery()
    {
        return (count($this->select));
    }

    /**
     * @return bool
     */
    private function isCreateQuery()
    {
        return (bool) $this->create;
    }

    /**
     * @return bool
     */
    private function isAlterQuery()
    {
        return (bool) $this->alter;
    }

    /**
     * @return bool
     */
    private function isDropQuery()
    {
        return (bool) $this->drop;
    }

    /**
     * @return mixed|void
     */
    public function build()
    {
        if ($this->isSelectQuery()) {

            $this->buildRaw();
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
    }
}
