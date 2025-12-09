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
     * @var array<int, UniqueDefinition|string>
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
     * @var array<int, CheckDefinition>
     */
    private array $addChecks = [];

    /**
     * @var array<int, CheckDefinition|string>
     */
    private array $dropChecks = [];

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
     * @param string|array<int, string> $columns
     * @return UniqueDefinition
     */
    public function addUnique(string|array $columns): UniqueDefinition
    {
        $unique = new UniqueDefinition($columns);
        $this->addUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string|array<int, string> $columns
     * @return UniqueDefinition
     */
    public function dropUnique(string|array $columns): UniqueDefinition
    {
        $unique = new UniqueDefinition($columns);
        $this->dropUniques[] = $unique;

        return $unique;
    }

    /**
     * @param string $identifier
     * @return void
     */
    public function dropUniqueByIdentifier(string $identifier): void
    {
        $this->dropUniques[] = $identifier;
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
     * Add a CHECK constraint to the table
     *
     * @param string $column The column name the check constraint applies to
     * @param string $expression The check expression (e.g., "IN (0, 1, 2, 3)" or "> 0")
     * @return CheckDefinition
     */
    public function addCheck(
        string $column,
        string $expression
    ): CheckDefinition {
        $check = new CheckDefinition($column, $expression);
        $this->addChecks[] = $check;

        return $check;
    }

    /**
     * Drop a CHECK constraint by column name
     *
     * @param string $column The column name the check constraint applies to
     * @param string $expression The check expression (not used, kept for consistency)
     * @return CheckDefinition
     */
    public function dropCheck(
        string $column,
        string $expression = ''
    ): CheckDefinition {
        $check = new CheckDefinition($column, $expression);
        $this->dropChecks[] = $check;

        return $check;
    }

    /**
     * Drop a CHECK constraint by its identifier name
     *
     * @param string $identifier The constraint identifier name
     * @return void
     */
    public function dropCheckByIdentifier(string $identifier): void
    {
        $this->dropChecks[] = $identifier;
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
     * Shorthand method for adding SEO-related columns
     *
     * Adds the following columns (if not set to empty string or null):
     * - seo_title VARCHAR(255) NULL
     * - seo_description VARCHAR(255) NULL
     * - seo_change_frequency VARCHAR(255) NULL
     * - seo_photo INT(11) with foreign key to dry_media_file
     * - seo_priority DECIMAL(10) NULL
     *
     * @param string|null $titleColumn Column name for SEO title (default: 'seo_title', use null or empty string to skip)
     * @param string|null $descriptionColumn Column name for SEO description (default: 'seo_description', use null or empty string to skip)
     * @param string|null $changeFrequencyColumn Column name for SEO change frequency (default: 'seo_change_frequency', use null or empty string to skip)
     * @param string|null $photoColumn Column name for SEO photo (default: 'seo_photo', use null or empty string to skip)
     * @param string|null $priorityColumn Column name for SEO priority (default: 'seo_priority', use null or empty string to skip)
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function seo(
        ?string $titleColumn = 'seo_title',
        ?string $descriptionColumn = 'seo_description',
        ?string $changeFrequencyColumn = 'seo_change_frequency',
        ?string $photoColumn = 'seo_photo',
        ?string $priorityColumn = 'seo_priority'
    ): self {
        if (!empty($titleColumn)) {
            $this->addColumn($titleColumn, 'varchar')->length(255)->null();
        }

        if (!empty($descriptionColumn)) {
            $this->addColumn($descriptionColumn, 'varchar')
                ->length(255)
                ->null();
        }

        if (!empty($changeFrequencyColumn)) {
            $this->addColumn($changeFrequencyColumn, 'varchar')
                ->length(255)
                ->null();
        }

        if (!empty($photoColumn)) {
            $this->addColumn($photoColumn, 'int')->length(11);
            $this->addForeignKey($photoColumn, 'dry_media_file', 'id');
        }

        if (!empty($priorityColumn)) {
            $this->addColumn($priorityColumn, 'decimal')->length(10)->null();
        }

        return $this;
    }

    /**
     * Shorthand method for dropping SEO-related columns
     *
     * Drops the following columns and constraints (if not set to empty string or null):
     * - seo_title
     * - seo_description
     * - seo_change_frequency
     * - seo_photo (with foreign key constraint)
     * - seo_priority
     *
     * @param string|null $titleColumn Column name for SEO title (default: 'seo_title', use null or empty string to skip)
     * @param string|null $descriptionColumn Column name for SEO description (default: 'seo_description', use null or empty string to skip)
     * @param string|null $changeFrequencyColumn Column name for SEO change frequency (default: 'seo_change_frequency', use null or empty string to skip)
     * @param string|null $photoColumn Column name for SEO photo (default: 'seo_photo', use null or empty string to skip)
     * @param string|null $priorityColumn Column name for SEO priority (default: 'seo_priority', use null or empty string to skip)
     * @return $this
     */
    public function dropSeo(
        ?string $titleColumn = 'seo_title',
        ?string $descriptionColumn = 'seo_description',
        ?string $changeFrequencyColumn = 'seo_change_frequency',
        ?string $photoColumn = 'seo_photo',
        ?string $priorityColumn = 'seo_priority'
    ): self {
        if (!empty($photoColumn)) {
            $this->dropForeignKey($photoColumn, 'dry_media_file', 'id');
            $this->dropColumn($photoColumn);
        }

        if (!empty($titleColumn)) {
            $this->dropColumn($titleColumn);
        }

        if (!empty($descriptionColumn)) {
            $this->dropColumn($descriptionColumn);
        }

        if (!empty($changeFrequencyColumn)) {
            $this->dropColumn($changeFrequencyColumn);
        }

        if (!empty($priorityColumn)) {
            $this->dropColumn($priorityColumn);
        }

        return $this;
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
                if (is_string($unique)) {
                    // Drop by identifier string
                    $columnStatement[] = 'DROP INDEX ' . $this->quote($unique);
                } else {
                    // Drop by UniqueDefinition object
                    $columnStatement[] =
                        'DROP INDEX ' . $this->quote($unique->getIdentifier());
                }
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

            foreach ($this->dropChecks as $check) {
                if (is_string($check)) {
                    // Drop by identifier string
                    $columnStatement[] = 'DROP CHECK ' . $this->quote($check);
                } else {
                    // Drop by CheckDefinition object
                    $columnStatement[] =
                        'DROP CHECK ' . $this->quote($check->getIdentifier());
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
            $columns = array_map(function ($col) {
                return $this->quote($col);
            }, $unique->getColumns());

            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') .
                'CONSTRAINT ' .
                $this->quote($unique->getIdentifier()) .
                ' UNIQUE (' .
                implode(', ', $columns) .
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

        foreach ($this->addChecks as $check) {
            $columnStatement[] =
                ($this->isAlter ? 'ADD ' : '') .
                'CONSTRAINT ' .
                $this->quote($check->getIdentifier()) .
                ' CHECK (' .
                $check->getExpression() .
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
                $this->addAdditionalQuery(
                    'DROP TRIGGER IF EXISTS ' . $this->quote($triggerName)
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
                $this->addAdditionalQuery(
                    'DROP TRIGGER IF EXISTS ' . $this->quote($insertTriggerName)
                );
                $this->addAdditionalQuery(
                    'DROP TRIGGER IF EXISTS ' . $this->quote($updateTriggerName)
                );

                // Create INSERT trigger for created timestamp
                $insertTriggerSql =
                    'CREATE TRIGGER ' .
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

                $this->addAdditionalQuery($insertTriggerSql);

                // Create UPDATE trigger for updated timestamp
                $updateTriggerSql =
                    'CREATE TRIGGER ' .
                    $this->quote($updateTriggerName) .
                    ' BEFORE UPDATE ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = UNIX_TIMESTAMP()';

                $this->addAdditionalQuery($updateTriggerSql);
            } else {
                // For datetime format, we need triggers for both INSERT and UPDATE
                $insertTriggerName = $tableName . '_created_trigger';
                $updateTriggerName = $tableName . '_updated_trigger';

                // Drop existing triggers
                $this->addAdditionalQuery(
                    'DROP TRIGGER IF EXISTS ' . $this->quote($insertTriggerName)
                );
                $this->addAdditionalQuery(
                    'DROP TRIGGER IF EXISTS ' . $this->quote($updateTriggerName)
                );

                // Create INSERT trigger for created timestamp
                $insertTriggerSql =
                    'CREATE TRIGGER ' .
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

                $this->addAdditionalQuery($insertTriggerSql);

                // Create UPDATE trigger for updated timestamp
                $updateTriggerSql =
                    'CREATE TRIGGER ' .
                    $this->quote($updateTriggerName) .
                    ' BEFORE UPDATE ON ' .
                    $this->quote($tableName) .
                    ' FOR EACH ROW SET NEW.' .
                    $this->quote($updatedColumn) .
                    ' = CURRENT_TIMESTAMP';

                $this->addAdditionalQuery($updateTriggerSql);
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
