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
    public function __construct(bool $isAlter = false) {
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
     * @var array $dropForeignKeyIdentifiers
     */
    private $dropForeignKeyIdentifiers = [];

    /**
     * @var array $addUniques
     */
    private $addUniques = [];

    /***
     * @var array $dropUniques
     */
    private $dropUniques = [];

    /**
     * @var ForeignKeyDefinition[] $addForeignKeys
     */
    private $addForeignKeys = [];

    /**
     * @param string $name
     * @param string $type
     * @return ColumnDefinition
     */
    public function addColumn(string $name, string $type) {
        $column = new ColumnDefinition($name);
        $column->type($type);
        $this->addColumns[] = $column;

        return $column;
    }

    /**
     * @param string $name
     * @return ColumnDefinition
     */
    public function changeColumn(string $name) {
        $column = new ColumnDefinition($name, true);
        $this->changeColumns[] = $column;

        return $column;
    }

    /**
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignColumn
     */
    public function dropForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = '') {
        $foreignKey = new ForeignKeyDefinition($this->getTable(), $column, $foreignTable, $foreignColumn, $onDelete, $onUpdate);
        $this->dropForeignKeys[] = $foreignKey;
    }

    /**
     * @param string $identifier
     */
    public function dropForeignKeyByIdentifier(string $identifier) {
        $this->dropForeignKeyIdentifiers[] = $identifier;
    }

    /**
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignColumn
     * @return ForeignKeyDefinition
     */
    public function addForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = '') {
        $foreignKey = new ForeignKeyDefinition($this->getTable(), $column, $foreignTable, $foreignColumn, $onDelete, $onUpdate);
        $this->addForeignKeys[] = $foreignKey;

        return $foreignKey;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function addUnique(string $column) {
        $unique = new UniqueDefinition($column);
        $this->addUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function dropUnique(string $column) {
        $unique = new UniqueDefinition($column);
        $this->dropUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $name
     */
    public function dropColumn(string $name) {
        $this->dropColumns[] = $name;
    }

    /**
     * @return mixed|void
     */
    public function build() {
        $columnStatement = [];

        foreach ($this->addColumns as $column) {
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '') . $column->getString();
        }

        if ($this->isAlter) {

            foreach ($this->changeColumns as $column) {
                $columnStatement[] = 'CHANGE ' . $column->getString();
            }

            foreach ($this->dropForeignKeys as $foreignKey) {
                $columnStatement[] = 'DROP INDEX ' . $this->quote($foreignKey->getIdentifier());
                $columnStatement[] = 'DROP FOREIGN KEY ' . $this->quote($foreignKey->getIdentifier());
            }

            foreach ($this->dropForeignKeyIdentifiers as $identifier) {
                $columnStatement[] = 'DROP INDEX ' . $this->quote($identifier);
                $columnStatement[] = 'DROP FOREIGN KEY ' . $this->quote($identifier);
            }

            foreach ($this->dropUniques as $unique) {
                $columnStatement[] = 'DROP INDEX ' . $this->quote($unique->getIdentifier());
            }

            foreach ($this->dropColumns as $column) {
                $columnStatement[] = 'DROP COLUMN ' . $this->quote($column);
            }
        }

        foreach ($this->addForeignKeys as $foreignKey) {
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '')
            . 'CONSTRAINT ' . $this->quote($foreignKey->getIdentifier())
            . ' FOREIGN KEY (' . $this->quote($foreignKey->getColumn()) . ') '
            . 'REFERENCES ' . $this->quote($foreignKey->getForeignTable()) . ' (' . $this->quote($foreignKey->getForeignColumn()) . ')'
            . (empty($foreignKey->getOnDelete()) ? '' : ' ON DELETE ' . $foreignKey->getOnDelete())
            . (empty($foreignKey->getOnUpdate()) ? '' : ' ON UPDATE ' . $foreignKey->getOnUpdate());
        }

        foreach ($this->addUniques as $unique) {
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '') . 'CONSTRAINT ' . $this->quote($unique->getIdentifier()) . ' UNIQUE (' . $this->quote($unique->getColumn()) . ')';
        }

        $this->addToQuery(implode(', ', $columnStatement));
    }
}