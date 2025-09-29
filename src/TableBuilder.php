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
     * @var array $timestamps
     */
    private $timestamps = [];

    /**
     * @var array $dropTriggers
     */
    private $dropTriggers = [];

    /**
     * @param string $name
     * @param string $type
     * @return ColumnDefinition
     * @throws \InvalidArgumentException
     */
    public function addColumn(string $name, string $type): ColumnDefinition {
        if (empty($name) || empty($type)) {
            throw new \InvalidArgumentException('Column name and type cannot be empty');
        }
        
        $column = new ColumnDefinition($name);
        $column->type($type);
        $this->addColumns[] = $column;

        return $column;
    }

    /**
     * @param string $name
     * @return ColumnDefinition
     * @throws \InvalidArgumentException
     */
    public function changeColumn(string $name): ColumnDefinition {
        if (empty($name)) {
            throw new \InvalidArgumentException('Column name cannot be empty');
        }
        
        $column = new ColumnDefinition($name, true);
        $this->changeColumns[] = $column;

        return $column;
    }

    /**
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignColumn
     * @param string $onDelete
     * @param string $onUpdate
     * @return void
     */
    public function dropForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = ''): void {
        $foreignKey = new ForeignKeyDefinition($this->getTable(), $column, $foreignTable, $foreignColumn, $onDelete, $onUpdate);
        $this->dropForeignKeys[] = $foreignKey;
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dropForeignKeyByIdentifier(string $identifier): void {
        $this->dropForeignKeyIdentifiers[] = $identifier;
    }

    /**
     * @param string $column
     * @param string $foreignTable
     * @param string $foreignColumn
     * @param string $onDelete
     * @param string $onUpdate
     * @return ForeignKeyDefinition
     * @throws \InvalidArgumentException
     */
    public function addForeignKey(string $column, string $foreignTable, string $foreignColumn = 'id', string $onDelete = '', string $onUpdate = ''): ForeignKeyDefinition {
        if (empty($column) || empty($foreignTable) || empty($foreignColumn)) {
            throw new \InvalidArgumentException('Column, foreign table, and foreign column cannot be empty');
        }
        
        $foreignKey = new ForeignKeyDefinition($this->getTable(), $column, $foreignTable, $foreignColumn, $onDelete, $onUpdate);
        $this->addForeignKeys[] = $foreignKey;

        return $foreignKey;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function addUnique(string $column): UniqueDefinition {
        $unique = new UniqueDefinition($column);
        $this->addUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function dropUnique(string $column): UniqueDefinition {
        $unique = new UniqueDefinition($column);
        $this->dropUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $name
     * @return void
     */
    public function dropColumn(string $name): void {
        $this->dropColumns[] = $name;
    }

    /**
     * @param string $createdColumn
     * @param string $updatedColumn
     * @return void
     */
    public function timestamps(string $createdColumn = 'created_at', string $updatedColumn = 'updated_at'): void {
        $this->timestamps = [
            'created' => $createdColumn,
            'updated' => $updatedColumn
        ];
    }

    /**
     * @param string $triggerName
     * @return void
     */
    public function dropTimestampTrigger(string $triggerName): void {
        $this->dropTriggers[] = $triggerName;
    }

    /**
     * Drop auto-generated timestamp triggers for this table
     * @return void
     */
    public function dropTimestampTriggers(): void {
        $tableName = $this->getTable();
        $this->dropTriggers[] = $tableName . '_updated_at_trigger';
    }

    /**
     * @return void
     */
    public function build(): void {
        $columnStatement = [];

        foreach ($this->addColumns as $column) {
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '') . $column->getString();
        }

        if (!empty($this->timestamps)) {
            $createdColumn = $this->timestamps['created'];
            $updatedColumn = $this->timestamps['updated'];
            
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '') . $this->quote($createdColumn) . ' TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
            $columnStatement[] = ($this->isAlter ? 'ADD ' : '') . $this->quote($updatedColumn) . ' TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
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

        $this->buildTriggers();
    }

    /**
     * @return void
     */
    private function buildTriggers(): void {
        if (!empty($this->dropTriggers)) {
            foreach ($this->dropTriggers as $triggerName) {
                $this->addToQuery('; DROP TRIGGER IF EXISTS ' . $this->quote($triggerName));
            }
        }

        // Note: For CREATE TABLE, MySQL's "ON UPDATE CURRENT_TIMESTAMP" handles updates automatically
        // Triggers are only needed for ALTER TABLE operations where we want to ensure consistency
        if (!empty($this->timestamps) && $this->isAlter) {
            $tableName = $this->getTable();
            $updatedColumn = $this->timestamps['updated'];
            
            $triggerName = $tableName . '_updated_at_trigger';
            
            $this->addToQuery('; DROP TRIGGER IF EXISTS ' . $this->quote($triggerName));
            
            $triggerSql = '; CREATE TRIGGER ' . $this->quote($triggerName) . 
                         ' BEFORE UPDATE ON ' . $this->quote($tableName) . 
                         ' FOR EACH ROW SET NEW.' . $this->quote($updatedColumn) . ' = CURRENT_TIMESTAMP';
            
            $this->addToQuery($triggerSql);
        }
    }

    /**
     * Get generated trigger names for cleanup
     * @return array
     */
    public function getGeneratedTriggerNames(): array {
        if (empty($this->timestamps)) {
            return [];
        }
        
        $tableName = $this->getTable();
        return [$tableName . '_updated_at_trigger'];
    }
}