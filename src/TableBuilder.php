<?php

namespace Tnt\Dbi;

class TableBuilder extends BuildHandler
{
    /**
     * @var bool
     */
    private $isAlter;

    /**
     * TableBuilder constructor.
     * @param bool $isAlter
     */
    public function __construct(bool $isAlter = false)
    {
        $this->isAlter = $isAlter;
    }

    /**
     * @var array $addColumns
     */
    private $addColumns = [];

    /**
     * @var array $changeColumns
     */
    private $changeColumns = [];

    /**
     * @var array $dropColumns
     */
    private $dropColumns = [];

    /**
     * @var array $dropForeignKeys
     */
    private $dropForeignKeys = [];

    /**
     * @var array $addForeignKeys
     */
    private $addForeignKeys = [];

    /**
     * @param string $name
     * @param string $type
     * @return ColumnDefinition
     */
    public function addColumn(string $name, string $type)
    {
        $column = new ColumnDefinition($name);
        $column->type($type);
        $this->addColumns[] = $column;

        return $column;
    }

    /**
     * @param string $name
     * @return ColumnDefinition
     */
    public function changeColumn(string $name)
    {
        $column = new ColumnDefinition($name, true);
        $this->changeColumns[] = $column;

        return $column;
    }

    /**
     * @param string $column
     * @param string $table
     */
    public function dropForeignKey(string $column, string $table)
    {
        $this->dropForeignKeys[] = 'fk_'.$column.'_'.$table;
    }

    /**
     * @param string $column
     * @param string $table
     * @param string $foreignColumn
     */
    public function addForeignKey(string $column, string $table, string $foreignColumn = 'id')
    {
        $this->addForeignKeys[] = [$column, $table, $foreignColumn,];
    }

    /**
     * @param string $name
     */
    public function dropColumn(string $name)
    {
        $this->dropColumns[] = $name;
    }

    public function build()
    {
        $columnStatement = [];

        foreach ($this->addColumns as $column) {
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '').$column->getString();
        }

        if ($this->isAlter) {

            foreach ($this->changeColumns as $column) {
                $columnStatement[] = 'CHANGE '.$column->getString();
            }

            foreach ($this->dropForeignKeys as $foreignKey) {
                $columnStatement[] = 'DROP INDEX '.$this->quote($foreignKey);
                $columnStatement[] = 'DROP FOREIGN KEY '.$this->quote($foreignKey);
            }

            foreach ($this->dropColumns as $column) {
                $columnStatement[] = 'DROP COLUMN '.$this->quote($column);
            }
        }

        foreach ($this->addForeignKeys as $foreignKey) {

            [$fkColumn, $fkTable, $fkForeignColumn] = $foreignKey;
            $constraintName = 'fk_'.$fkColumn.'_'.$fkTable;

            $columnStatement[] = ($this->isAlter ? 'ADD ' : '').'CONSTRAINT `'.$constraintName.'` FOREIGN KEY ('.$this->quote($fkColumn).') REFERENCES '.$this->quote($fkTable).' ('.$this->quote($fkForeignColumn).')';
        }

        $this->addToQuery(implode(', ', $columnStatement));
    }
}