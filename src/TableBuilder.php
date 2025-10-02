<?php

namespace Tnt\Dbi;

use Tnt\Dbi\Enums\TimestampFormat;

class TableBuilder extends BuildHandler
{
    /**
     * @var bool
     */
    private bool $isAlter;

    /**
     * TableBuilder constructor.
     * @param bool $isAlter
     */
    public function __construct(bool $isAlter = false)
    {
        $this->isAlter = $isAlter;
    }

    /**
     * @var array<int, ColumnDefinition>
     */
    private array $addColumns = [];

    /**
     * @var array<int, ColumnDefinition>
     */
    private array $changeColumns = [];

    /**
     * @var array<int, string>
     */
    private array $dropColumns = [];

    /**
     * @var array<int, ForeignKeyDefinition>
     */
    private array $dropForeignKeys = [];

    /**
     * @var array<int, string>
     */
    private array $dropForeignKeyIdentifiers = [];

    /**
     * @var array<int, UniqueDefinition>
     */
    private array $addUniques = [];

    /***
     * @var array<int, string>
     */
    private array $dropUniques = [];

    /**
     * @var array<int, ForeignKeyDefinition>
     */
    private array $addForeignKeys = [];

    /**
     * @var array<string, string>
     */
    private array $timestamps = [];

    /**
     * @var TimestampFormat
     */
    private TimestampFormat $timestampFormat = TimestampFormat::UNIX;

    /**
     * @var array<int, string>
     */
    private array $dropTriggers = [];

    /**
     * @var array<int, IndexDefinition>
     */
    private array $addIndexes = [];

    /**
     * @var array<int, IndexDefinition|string>
     */
    private array $dropIndexes = [];

    /**
     * @param string $name
     * @param string $type
     * @return ColumnDefinition
     * @throws \InvalidArgumentException
     */
    public function addColumn(string $name, string $type): ColumnDefinition
    {
        if (empty($name) || empty($type)) {
            throw new \InvalidArgumentException(
                'Column name and type cannot be empty'
            );
        }

        $column = new ColumnDefinition($name);
        $column->type($type);
        $this->addColumns[] = $column;

        return $column;
    }

    /**
     * Shorthand method for creating a primary key column
     * @param string $name
     * @param string $type
     * @param int $length
     * @param bool $autoIncrement
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function id(
        string $name = 'id',
        string $type = 'int',
        int $length = 11,
        bool $autoIncrement = true
    ): self {
        $this->addColumn($name, $type)
            ->length($length)
            ->primaryKey($autoIncrement);
        return $this;
    }

    /**
     * @param string $name
     * @return ColumnDefinition
     * @throws \InvalidArgumentException
     */
    public function changeColumn(string $name): ColumnDefinition
    {
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
    public function dropForeignKey(
        string $column,
        string $foreignTable,
        string $foreignColumn = 'id',
        string $onDelete = '',
        string $onUpdate = ''
    ): void {
        $foreignKey = new ForeignKeyDefinition(
            $this->getTable(),
            $column,
            $foreignTable,
            $foreignColumn,
            $onDelete,
            $onUpdate
        );
        $this->dropForeignKeys[] = $foreignKey;
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dropForeignKeyByIdentifier(string $identifier): void
    {
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
    public function addForeignKey(
        string $column,
        string $foreignTable,
        string $foreignColumn = 'id',
        string $onDelete = '',
        string $onUpdate = ''
    ): ForeignKeyDefinition {
        if (empty($column) || empty($foreignTable) || empty($foreignColumn)) {
            throw new \InvalidArgumentException(
                'Column, foreign table, and foreign column cannot be empty'
            );
        }

        $foreignKey = new ForeignKeyDefinition(
            $this->getTable(),
            $column,
            $foreignTable,
            $foreignColumn,
            $onDelete,
            $onUpdate
        );
        $this->addForeignKeys[] = $foreignKey;

        return $foreignKey;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function addUnique(string $column): UniqueDefinition
    {
        $unique = new UniqueDefinition($column);
        $this->addUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $column
     * @return UniqueDefinition
     */
    public function dropUnique(string $column): UniqueDefinition
    {
        $unique = new UniqueDefinition($column);
        $this->dropUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string|array<int, string> $columns
     * @return IndexDefinition
     */
    public function addIndex(string|array $columns): IndexDefinition
    {
        $index = new IndexDefinition($columns);
        $this->addIndexes[] = $index;

        return $index;
    }

    /**
     * @param string|array<int, string> $columns
     * @return IndexDefinition
     */
    public function dropIndex(string|array $columns): IndexDefinition
    {
        $index = new IndexDefinition($columns);
        $this->dropIndexes[] = $index;

        return $index;
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dropIndexByIdentifier(string $identifier): void
    {
        $this->dropIndexes[] = $identifier;
    }

    /**
     * @param string $name
     * @return void
     */
    public function dropColumn(string $name): void
    {
        $this->dropColumns[] = $name;
    }

    /**
     * Add automatic timestamp columns with MySQL triggers
     *
     * @param string $createdColumn The name of the created timestamp column (default: 'created')
     * @param string $updatedColumn The name of the updated timestamp column (default: 'updated')
     * @param TimestampFormat $format The timestamp format: TimestampFormat::UNIX (INT UNSIGNED - default) or TimestampFormat::DATETIME (TIMESTAMP)
     * @return void
     */
    public function timestamps(
        string $createdColumn = 'created',
        string $updatedColumn = 'updated',
        TimestampFormat $format = TimestampFormat::UNIX
    ): void {
        $this->timestamps = [
            'created' => $createdColumn,
            'updated' => $updatedColumn,
        ];
        $this->timestampFormat = $format;
    }

    /**
     * @param string $triggerName
     * @return void
     */
    public function dropTimestampTrigger(string $triggerName): void
    {
        $this->dropTriggers[] = $triggerName;
    }

    /**
     * Drop auto-generated timestamp triggers for this table
     * @return void
     */
    public function dropTimestampTriggers(): void
    {
        $tableName = $this->getTable();
        $this->dropTriggers[] = $tableName . '_created_trigger';
        $this->dropTriggers[] = $tableName . '_updated_trigger';
    }

    /**
     * @return void
     */
    public function build(): void
    {
        $columnStatement = [];

        foreach ($this->addColumns as $column) {
            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') . $column->getString();
        }

        if (!empty($this->timestamps)) {
            $createdColumn = $this->timestamps['created'];
            $updatedColumn = $this->timestamps['updated'];

            if ($this->timestampFormat === TimestampFormat::UNIX) {
                $columnStatement[] =
                    ($this->isAlter ? 'ADD ' : '') .
                    $this->quote($createdColumn) .
                    ' INT UNSIGNED NOT NULL';
                $columnStatement[] =
                    ($this->isAlter ? 'ADD ' : '') .
                    $this->quote($updatedColumn) .
                    ' INT UNSIGNED NOT NULL';
            } else {
                // Use TIMESTAMP NOT NULL for both columns, triggers will handle the values
                $columnStatement[] =
                    ($this->isAlter ? 'ADD ' : '') .
                    $this->quote($createdColumn) .
                    ' TIMESTAMP NOT NULL';
                $columnStatement[] =
                    ($this->isAlter ? 'ADD ' : '') .
                    $this->quote($updatedColumn) .
                    ' TIMESTAMP NOT NULL';
            }
        }

        if ($this->isAlter) {
            foreach ($this->changeColumns as $column) {
                $columnStatement[] = 'CHANGE ' . $column->getString();
            }

            foreach ($this->dropForeignKeys as $foreignKey) {
                $columnStatement[] =
                    'DROP INDEX ' . $this->quote($foreignKey->getIdentifier());
                $columnStatement[] =
                    'DROP FOREIGN KEY ' .
                    $this->quote($foreignKey->getIdentifier());
            }

            foreach ($this->dropForeignKeyIdentifiers as $identifier) {
                $columnStatement[] = 'DROP INDEX ' . $this->quote($identifier);
                $columnStatement[] =
                    'DROP FOREIGN KEY ' . $this->quote($identifier);
            }

            foreach ($this->dropUniques as $unique) {
                $columnStatement[] =
                    'DROP INDEX ' . $this->quote($unique->getIdentifier());
            }

            foreach ($this->dropIndexes as $index) {
                if (is_string($index)) {
                    // Drop by identifier string
                    $columnStatement[] = 'DROP INDEX ' . $this->quote($index);
                } else {
                    // Drop by IndexDefinition object
                    $columnStatement[] =
                        'DROP INDEX ' . $this->quote($index->getIdentifier());
                }
            }

            foreach ($this->dropColumns as $column) {
                $columnStatement[] = 'DROP COLUMN ' . $this->quote($column);
            }
        }

        foreach ($this->addForeignKeys as $foreignKey) {
            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') .
                'CONSTRAINT ' .
                $this->quote($foreignKey->getIdentifier()) .
                ' FOREIGN KEY (' .
                $this->quote($foreignKey->getColumn()) .
                ') ' .
                'REFERENCES ' .
                $this->quote($foreignKey->getForeignTable()) .
                ' (' .
                $this->quote($foreignKey->getForeignColumn()) .
                ')' .
                (empty($foreignKey->getOnDelete())
                    ? ''
                    : ' ON DELETE ' . $foreignKey->getOnDelete()) .
                (empty($foreignKey->getOnUpdate())
                    ? ''
                    : ' ON UPDATE ' . $foreignKey->getOnUpdate());
        }

        foreach ($this->addUniques as $unique) {
            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') .
                'CONSTRAINT ' .
                $this->quote($unique->getIdentifier()) .
                ' UNIQUE (' .
                $this->quote($unique->getColumn()) .
                ')';
        }

        foreach ($this->addIndexes as $index) {
            $columns = array_map(function ($col) {
                return $this->quote($col);
            }, $index->getColumns());

            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') .
                'INDEX ' .
                $this->quote($index->getIdentifier()) .
                ' (' .
                implode(', ', $columns) .
                ')';
        }

        $this->addToQuery(implode(', ', $columnStatement));

        $this->buildTriggers();
    }

    /**
     * @return void
     */
    private function buildTriggers(): void
    {
        if (!empty($this->dropTriggers)) {
            foreach ($this->dropTriggers as $triggerName) {
                $this->addToQuery(
                    '; DROP TRIGGER IF EXISTS ' . $this->quote($triggerName)
                );
            }
        }

        if (!empty($this->timestamps)) {
            $tableName = $this->getTable();
            $createdColumn = $this->timestamps['created'];
            $updatedColumn = $this->timestamps['updated'];

            if ($this->timestampFormat === TimestampFormat::UNIX) {
                // For Unix timestamps, we need triggers for both INSERT and UPDATE
                $insertTriggerName = $tableName . '_created_trigger';
                $updateTriggerName = $tableName . '_updated_trigger';

                // Drop existing triggers
                $this->addToQuery(
                    '; DROP TRIGGER IF EXISTS ' .
                        $this->quote($insertTriggerName)
                );
                $this->addToQuery(
                    '; DROP TRIGGER IF EXISTS ' .
                        $this->quote($updateTriggerName)
                );

                // Create INSERT trigger for created timestamp
                $insertTriggerSql =
                    '; CREATE TRIGGER ' .
                    $this->quote($insertTriggerName) .
                    ' BEFORE INSERT ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW BEGIN' .
                    ' SET NEW.' .
                    $this->quote($createdColumn) .
                    ' = UNIX_TIMESTAMP();' .
                    ' SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = UNIX_TIMESTAMP();' .
                    ' END';

                $this->addToQuery($insertTriggerSql);

                // Create UPDATE trigger for updated timestamp
                $updateTriggerSql =
                    '; CREATE TRIGGER ' .
                    $this->quote($updateTriggerName) .
                    ' BEFORE UPDATE ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = UNIX_TIMESTAMP()';

                $this->addToQuery($updateTriggerSql);
            } else {
                // For datetime format, we need triggers for both INSERT and UPDATE
                $insertTriggerName = $tableName . '_created_trigger';
                $updateTriggerName = $tableName . '_updated_trigger';

                // Drop existing triggers
                $this->addToQuery(
                    '; DROP TRIGGER IF EXISTS ' .
                        $this->quote($insertTriggerName)
                );
                $this->addToQuery(
                    '; DROP TRIGGER IF EXISTS ' .
                        $this->quote($updateTriggerName)
                );

                // Create INSERT trigger for created timestamp
                $insertTriggerSql =
                    '; CREATE TRIGGER ' .
                    $this->quote($insertTriggerName) .
                    ' BEFORE INSERT ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW BEGIN' .
                    ' SET NEW.' .
                    $this->quote($createdColumn) .
                    ' = CURRENT_TIMESTAMP;' .
                    ' SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = CURRENT_TIMESTAMP;' .
                    ' END';

                $this->addToQuery($insertTriggerSql);

                // Create UPDATE trigger for updated timestamp
                $updateTriggerSql =
                    '; CREATE TRIGGER ' .
                    $this->quote($updateTriggerName) .
                    ' BEFORE UPDATE ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = CURRENT_TIMESTAMP';

                $this->addToQuery($updateTriggerSql);
            }
        }
    }

    /**
     * Get generated trigger names for cleanup
     * @return array
     */
    /**
     * @return array<int, string>
     */
    public function getGeneratedTriggerNames(): array
    {
        if (empty($this->timestamps)) {
            return [];
        }

        $tableName = $this->getTable();

        // Both datetime and unix formats now use two triggers
        return [
            $tableName . '_created_trigger',
            $tableName . '_updated_trigger',
        ];
    }
}
