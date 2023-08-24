<?php

namespace Tnt\Dbi;

class ForeignKeyDefinition
{
    /**
     * @var string $table
     */
    private $table;

    /**
     * @var string $column
     */
    private $column;

    /**
     * @var string $foreignTable
     */
    private $foreignTable;

    /**
     * @var string $foreignColumn
     */
    private $foreignColumn;

    /**
     * @var string $identifierName
     */
    private $identifierName;

    /**
     * @var string $onDelete
     */
    private $onDelete;

    /**
     * @var string $onUpdate
     */
    private $onUpdate;

    /**
     * ForeignKeyDefinition constructor.
     * @param string $table
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignColumn
     * @param string $onDelete
     * @param string $onUpdate
     */
    public function __construct(string $table, string $column, string $foreignTable, string $foreignColumn, string $onDelete, string $onUpdate)
    {
        $this->table = $table;
        $this->column = $column;
        $this->foreignTable = $foreignTable;
        $this->foreignColumn = $foreignColumn;
        $this->onDelete = $onDelete;
        $this->onUpdate = $onUpdate;
    }

    /**
     * @param string $identifierName
     * @return $this
     */
    public function identifier(string $identifierName)
    {
        $this->identifierName = $identifierName;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return ($this->identifierName ?: 'fk_'.$this->table.'_'.$this->column.'_'.$this->foreignTable.'_'.$this->foreignColumn);
    }

    /**
     * @return string
     */
    public function getForeignColumn()
    {
        return $this->foreignColumn;
    }

    /**
     * @return string
     */
    public function getForeignTable()
    {
        return $this->foreignTable;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->column;
    }

    public function getOnDelete(): string {
        return $this->onDelete;
    }

    public function getOnUpdate(): string {
        return $this->onUpdate;
    }
}